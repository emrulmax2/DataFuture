<?php

namespace App\Console\Commands;

use App\Services\Sso\ClientRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Explains why single sign-on logout broadcasting is or is not working.
 *
 * The broadcast is deliberately fire-and-forget - a relying application being
 * unreachable must never block someone signing out - which means failures only
 * ever surface as a log line. This command performs the same call in the
 * foreground and shows exactly what happens.
 */
class SsoDiagnose extends Command
{
    protected $signature = 'sso:diagnose
                            {--email= : Send a REAL logout broadcast for this address instead of a harmless probe}';

    protected $description = 'Diagnose SSO configuration and logout-broadcast connectivity';

    public function handle(): int
    {
        $this->line('');
        $this->info('== Identity provider configuration ==');

        $rows = [
            ['server_enabled', var_export(config('sso.server_enabled'), true)],
            ['broadcast_logout', var_export(config('sso.broadcast_logout'), true)],
            ['guard', (string) config('sso.guard')],
            ['login_route', (string) config('sso.login_route')],
            ['ticket_ttl', (string) config('sso.ticket_ttl')],
            ['ticket_store', (string) config('sso.ticket_store')],
            ['logout_timeout', (string) config('sso.logout_timeout')],
        ];
        $this->table(['setting', 'value'], $rows);

        if (! config('sso.server_enabled')) {
            $this->error('server_enabled is false - no tickets are issued and nothing is broadcast.');
            $this->line('Set SSO_SERVER_ENABLED=true, then run: php artisan config:clear');

            return self::FAILURE;
        }

        if (! config('sso.broadcast_logout')) {
            $this->error('broadcast_logout is false - sign-out will never notify relying applications.');
            $this->line('Set SSO_BROADCAST_LOGOUT=true, then run: php artisan config:clear');

            return self::FAILURE;
        }

        $this->line('');
        $this->info('== PHP transport ==');
        $this->line('  curl extension : '.(extension_loaded('curl') ? 'loaded' : 'MISSING'));
        $this->line('  allow_url_fopen: '.(ini_get('allow_url_fopen') ? 'on' : 'off'));
        if (! extension_loaded('curl') && ! ini_get('allow_url_fopen')) {
            $this->error('  No usable HTTP transport - Guzzle cannot make outbound requests at all.');
        }

        $clients = ClientRegistry::all();

        if ($clients === []) {
            $this->error('No SSO clients are registered. Check the SSO_CLIENT_* keys in .env.');

            return self::FAILURE;
        }

        $email = $this->option('email');
        $failed = false;

        foreach ($clients as $client) {
            $this->line('');
            $this->info('== Client: '.$client['id'].' ('.$client['name'].') ==');
            $this->line('  secret     : set, '.strlen($client['secret']).' chars');
            $this->line('  domains    : '.(implode(', ', $client['domains']) ?: '(any)'));
            $this->line('  redirects  : '.implode(', ', $client['redirects']));
            $this->line('  logout_url : '.($client['logout_url'] ?: 'NOT SET'));

            if (! $client['logout_url']) {
                $this->error('  No logout URL, so this client is never notified on sign-out.');
                $failed = true;
                continue;
            }

            // Without --email we send a deliberately invalid signature. A well
            // behaved client answers 401 without logging anyone out, which
            // proves the network path end to end and is completely harmless.
            $probeEmail = $email ?: 'connectivity-probe@'.($client['domains'][0] ?? 'lcc.ac.uk');
            $issuedAt = time();
            $sid = 'diagnose';

            $signature = $email
                ? hash_hmac('sha256', $client['id'].'|'.$email.'|'.$sid.'|'.$issuedAt, $client['secret'])
                : 'deliberately-invalid-probe-signature';

            $this->line('  sending    : '.($email
                ? 'REAL logout for '.$email
                : 'harmless probe (expect HTTP 401 invalid_signature)'));

            $started = microtime(true);

            try {
                $response = Http::timeout(max(1, (int) config('sso.logout_timeout', 5)))
                    ->asJson()
                    ->acceptJson()
                    ->post($client['logout_url'], [
                        'client_id' => $client['id'],
                        'email'     => $probeEmail,
                        'sid'       => $sid,
                        'issued_at' => $issuedAt,
                        'signature' => $signature,
                    ]);

                $ms = round((microtime(true) - $started) * 1000);
                $this->line('  result     : HTTP '.$response->status().'  ('.$ms.' ms)');
                $this->line('  body       : '.trim(substr($response->body(), 0, 300)));

                $failed = $this->interpret($response->status(), (bool) $email) || $failed;
            } catch (\Throwable $e) {
                $ms = round((microtime(true) - $started) * 1000);
                $this->error('  FAILED after '.$ms.' ms');
                $this->error('  '.get_class($e));
                $this->error('  '.$e->getMessage());
                $this->line('');
                $this->warn('  This is why sign-out does not propagate. Common causes on a');
                $this->warn('  cPanel/WHM host, in the order worth checking:');
                $this->warn('   1. Outbound HTTPS blocked by the firewall (CSF: allow TCP out on 443).');
                $this->warn('   2. The server cannot reach its own public hostname (NAT hairpin).');
                $this->warn('      Workaround: point SSO_CLIENT_*_LOGOUT_URL at the internal address,');
                $this->warn('      or add a hosts entry for operations.lcc.ac.uk.');
                $this->warn('   3. Stale CA bundle, so the TLS handshake fails (update ca-certificates,');
                $this->warn('      or set curl.cainfo in php.ini).');
                $this->warn('   4. DNS not resolving from the server (test: getent hosts <host>).');
                $failed = true;
            }
        }

        $this->line('');

        if ($failed) {
            $this->error('One or more clients could not be notified. See above.');

            return self::FAILURE;
        }

        $this->info('All clients reachable. Logout broadcasting should be working.');

        return self::SUCCESS;
    }

    /**
     * @return bool whether this status should be treated as a failure
     */
    private function interpret(int $status, bool $wasReal): bool
    {
        if ($wasReal) {
            if ($status === 200) {
                $this->info('  OK - the client accepted the logout and dropped the sessions.');

                return false;
            }

            $this->error('  The client rejected a correctly signed logout.');
            $this->warn('  401 invalid_signature => the two secrets do not match.');
            $this->warn('  400 stale_request     => server clocks are out of sync.');

            return true;
        }

        if ($status === 401) {
            $this->info('  OK - reachable, and it correctly rejected an invalid signature.');
            $this->line('  Network path is fine. Re-run with --email=someone@lcc.ac.uk to test for real.');

            return false;
        }

        if ($status === 404) {
            $this->error('  404 - the client has no /api/sso/logout route deployed.');

            return true;
        }

        if ($status === 419 || $status === 302) {
            $this->error('  '.$status.' - the endpoint is behind session/CSRF middleware.');
            $this->warn('  It must sit on the stateless api group, not the web group.');

            return true;
        }

        $this->error('  Unexpected status - the endpoint is not behaving as an SSO client.');

        return true;
    }
}
