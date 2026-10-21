<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\HrVacancySyncController;
use App\Models\HrVacancy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Verifies the endpoint the LCC Operations app calls to list a published
 * internal vacancy under HR Portal > Vacancies.
 *
 * Like InterviewDocumentSyncTest it runs on a private in-memory sqlite
 * connection with just the tables the controller touches, and calls the
 * controller directly to bypass the Passport client-credentials middleware.
 */
class HrVacancySyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.sqlite_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        config()->set('database.default', 'sqlite_testing');
        config()->set('services.sms_sync.created_by', 1);
        DB::purge('sqlite_testing');

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name')->nullable();
            $t->string('email')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('hr_vacancy_types', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('hr_vacancies', function (Blueprint $t) {
            $t->id();
            $t->string('ops_requisition_ref', 32)->nullable()->unique();
            $t->unsignedBigInteger('hr_vacancy_type_id')->nullable();
            $t->string('title');
            $t->text('link')->nullable();
            $t->date('date')->nullable();
            $t->string('document')->nullable();
            $t->smallInteger('active')->default(1);
            $t->unsignedBigInteger('created_by');
            $t->unsignedBigInteger('updated_by')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });

        foreach (['Internal', 'External', 'Both'] as $name) {
            DB::table('hr_vacancy_types')->insert(['name' => $name, 'created_by' => 1]);
        }

        DB::table('users')->insert(['id' => 1, 'name' => 'System', 'email' => 'system@lcc.ac.uk']);
        DB::table('users')->insert(['id' => 7, 'name' => 'Hana', 'email' => 'hana.hr@lcc.ac.uk']);
    }

    private function push(array $overrides = [])
    {
        $request = Request::create('/api/hr/vacancies/push', 'POST', $overrides + [
            'reference' => 'REQ-2026-003',
            'title'     => 'Academic Administrator',
            'type'      => 'Internal',
            'link'      => 'https://operations.lcc.ac.uk/d/recruitment/vacancies/3',
            'date'      => '2026-11-02',
            'active'    => true,
            'created_by_email' => 'hana.hr@lcc.ac.uk',
        ]);

        return app(HrVacancySyncController::class)->store($request);
    }

    public function test_it_lists_the_vacancy_with_its_link_and_the_publisher_as_author(): void
    {
        $response = $this->push();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('created', $response->getData(true)['status']);

        $row = HrVacancy::firstOrFail();

        $this->assertSame('REQ-2026-003', $row->ops_requisition_ref);
        $this->assertSame('Academic Administrator', $row->title);
        $this->assertSame('https://operations.lcc.ac.uk/d/recruitment/vacancies/3', $row->link);
        $this->assertSame('Internal', $row->type->name);
        $this->assertSame('02-11-2026', $row->date);
        $this->assertSame(1, (int) $row->active);
        $this->assertSame(7, (int) $row->created_by);
    }

    public function test_pushing_again_updates_the_same_row_and_can_switch_it_off(): void
    {
        $this->push();
        $second = $this->push(['type' => 'Both', 'active' => false, 'created_by_email' => 'nobody@example.org']);

        $this->assertSame('updated', $second->getData(true)['status']);
        $this->assertSame(1, HrVacancy::count());

        $row = HrVacancy::firstOrFail();

        $this->assertSame('Both', $row->type->name);
        $this->assertSame(0, (int) $row->active);
        $this->assertSame(1, (int) $row->updated_by, 'An unknown publisher falls back to the system user.');
        $this->assertSame(7, (int) $row->created_by);
    }

    public function test_a_vacancy_deleted_here_is_not_brought_back(): void
    {
        $this->push();
        HrVacancy::firstOrFail()->delete();

        $response = $this->push(['title' => 'Changed']);

        $this->assertSame('deleted_here', $response->getData(true)['status']);
        $this->assertSame(0, HrVacancy::count());
        $this->assertSame('Academic Administrator', HrVacancy::withTrashed()->firstOrFail()->title);
    }
}
