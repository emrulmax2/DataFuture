<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommonSmtpRequest;
use App\Http\Requests\ComonSmtpUpdateRequest;
use App\Models\ComonSmtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Throwable;

class CommonSmtpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('pages.settings.smtp.index', [
            'title' => 'SMTP Settings - London Churchill College',
            'subtitle' => 'Communication Settings',
            'breadcrumbs' => [
                ['label' => 'Site Settings', 'href' => route('site.setting')],
                ['label' => 'SMTP Settings', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = ComonSmtp::orderByRaw(implode(',', $sorts))->where('account_type', 0);
        if(!empty($queryStr)):
            $query->where('name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        
        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();
        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'smtp_user' => $list->smtp_user,
                    'smtp_pass' => $list->smtp_pass,
                    'smtp_email_password' => $list->smtp_email_password,
                    'smtp_host' => $list->smtp_host,
                    'smtp_port' => $list->smtp_port,
                    'smtp_encryption' => strtoupper($list->smtp_encryption),
                    'smtp_authentication' => strtoupper($list->smtp_authentication),
                    'is_default' => $list->is_default,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommonSmtpRequest $request)
    {
        $is_default = (isset($request->is_default) && $request->is_default > 0 ? $request->is_default : 0);
        $request->request->remove('is_default');
        $request->request->add(['account_type' => 0, 'is_default' => $is_default, 'created_by' => auth()->user()->id]);
        $commonSmtp = ComonSmtp::create($request->all());

        if($commonSmtp){
            if($is_default == 1):
                $default = ComonSmtp::where('id', '!=', $commonSmtp->id)->update(['is_default' => 0]);
            endif;
            return response()->json(['message' => 'Data successfullly inserted'], 200);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DocumentSettings  $documentSettings
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocumentSettings  $documentSettings
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $smtp = ComonSmtp::find($id);
        return response()->json($smtp);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DocumentSettings  $documentSettings
     * @return \Illuminate\Http\Response
     */
    public function update(ComonSmtpUpdateRequest $request, ComonSmtp $dataId)
    {
        $is_default = (isset($request->is_default) && $request->is_default > 0 ? $request->is_default : 0);
        $request->request->remove('is_default');
        $request->request->add(['is_default' => $is_default, 'updated_by' => auth()->user()->id]);
        $ComonSmtp = ComonSmtp::where('id', $request->id)->update($request->all());

        if($is_default == 1):
            $default = ComonSmtp::where('id', '!=', $request->id)->update(['is_default' => 0]);
        endif;

        return response()->json(['message' => 'Data successfully updated'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocumentSettings  $documentSettings
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = ComonSmtp::find($id)->delete();
        return response()->json($data);
    }

    public function restore($id) {
        $data = ComonSmtp::where('id', $id)->withTrashed()->restore();

        response()->json($data);
    }

    /**
     * Probe the given SMTP accounts against their provider and report which ones
     * can actually connect + authenticate. Results are cached per credential set
     * so opening the list repeatedly does not hammer the mail provider (which can
     * trip rate limiting / security blocks on Gmail and friends).
     */
    public function health(Request $request)
    {
        $ids = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->take(50)
            ->all();

        if (empty($ids)) {
            return response()->json(['data' => []]);
        }

        $accounts = ComonSmtp::withTrashed()->whereIn('id', $ids)->get();
        $data = [];

        foreach ($accounts as $account) {
            $signature = md5(implode('|', [
                $account->smtp_user,
                $account->smtp_pass,
                $account->smtp_host,
                $account->smtp_port,
                $account->smtp_encryption,
                $account->smtp_authentication,
            ]));

            $data[$account->id] = Cache::remember(
                'smtp-health:'.$account->id.':'.$signature,
                now()->addMinutes(10),
                fn () => $this->probeSmtp($account)
            );
        }

        return response()->json(['data' => $data]);
    }

    private function probeSmtp(ComonSmtp $account): array
    {
        if (empty($account->smtp_host) || empty($account->smtp_port)) {
            return ['ok' => false, 'message' => 'Host or port is missing.'];
        }

        $transport = null;

        try {
            $port = (int) $account->smtp_port;
            $useTls = strtolower((string) $account->smtp_encryption) === 'ssl';

            $transport = new EsmtpTransport($account->smtp_host, $port, $useTls);

            $stream = $transport->getStream();
            if ($stream instanceof SocketStream) {
                $stream->setTimeout(5);
            }

            if (strtolower((string) $account->smtp_authentication) !== 'false') {
                $transport->setUsername((string) $account->smtp_user);
                $transport->setPassword((string) $account->smtp_pass);
            }

            $transport->start();

            return ['ok' => true, 'message' => 'Connected successfully.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => trim($e->getMessage()) ?: 'Unable to connect.'];
        } finally {
            if ($transport !== null) {
                try {
                    $transport->stop();
                } catch (Throwable $e) {
                    // Nothing useful to do if the socket is already gone.
                }
            }
        }
    }
}
