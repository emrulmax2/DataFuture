<?php

namespace Tests\Feature;

use App\Models\CourseCreation;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Course Creation › Induction Booking Link (user, 30 Oct 2026): on the add/edit form after the
 * Venues box, saved by store and update. (Moved here from Term Declaration the same day.)
 */
class CourseCreationInductionLinkTest extends TestCase
{
    use DatabaseTransactions;

    private User $staff;
    private CourseCreation $sample;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::find(7);
        $this->sample = CourseCreation::whereHas('venues')->orderByDesc('id')->firstOrFail();
    }

    private function fields(array $overrides = []): array
    {
        return array_merge([
            'semester_id' => $this->sample->semester_id,
            'course_id' => $this->sample->course_id,
            'course_creation_qualification_id' => $this->sample->course_creation_qualification_id,
            'duration' => 2,
            'unit_length' => 'Years',
            'venue_id' => [Venue::orderBy('id')->value('id')],
            'slc_code' => ['TEST1'],
            'weekdays' => [3],
            'induction_booking_link' => 'https://lcc.ac.uk/induction/book?course=hnd',
        ], $overrides);
    }

    public function test_the_field_comes_after_the_venues_box_in_both_forms(): void
    {
        $html = $this->actingAs($this->staff)->get(route('course.creation'))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'name="induction_booking_link"'));
        $venues = strpos($html, 'data-cm-venues');
        $this->assertNotFalse($venues);
        $this->assertGreaterThan($venues, strpos($html, 'name="induction_booking_link"'));
    }

    public function test_store_saves_it_and_edit_returns_it(): void
    {
        $this->actingAs($this->staff)->post(route('course.creation.store'), $this->fields(), ['Accept' => 'application/json'])->assertOk();

        $creation = CourseCreation::latest('id')->firstOrFail();
        $this->assertSame('https://lcc.ac.uk/induction/book?course=hnd', $creation->induction_booking_link);

        $this->actingAs($this->staff)->getJson(route('course.creation.edit', $creation->id))
            ->assertOk()
            ->assertJsonPath('induction_booking_link', 'https://lcc.ac.uk/induction/book?course=hnd');
    }

    public function test_it_is_optional_and_must_be_a_full_link(): void
    {
        $this->actingAs($this->staff)->post(route('course.creation.store'), $this->fields(['induction_booking_link' => 'lcc.ac.uk/book']), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('errors.induction_booking_link.0', 'Please enter the full link, starting with https://');

        $this->actingAs($this->staff)->post(route('course.creation.store'), $this->fields(['induction_booking_link' => '']), ['Accept' => 'application/json'])->assertOk();
        $this->assertNull(CourseCreation::latest('id')->firstOrFail()->induction_booking_link);
    }

    public function test_update_changes_and_clears_it(): void
    {
        $this->actingAs($this->staff)->post(route('course.creation.store'), $this->fields(), ['Accept' => 'application/json'])->assertOk();
        $creation = CourseCreation::latest('id')->firstOrFail();

        $this->actingAs($this->staff)->post(route('course.creation.update'), $this->fields(['id' => $creation->id, 'induction_booking_link' => 'https://lcc.ac.uk/induction/new']), ['Accept' => 'application/json'])
            ->assertOk();
        $this->assertSame('https://lcc.ac.uk/induction/new', $creation->fresh()->induction_booking_link);

        $this->travel(1)->seconds(); // a later updated_at, so the second update counts as a change
        $this->actingAs($this->staff)->post(route('course.creation.update'), $this->fields(['id' => $creation->id, 'induction_booking_link' => '']), ['Accept' => 'application/json'])
            ->assertOk();
        $this->assertNull($creation->fresh()->induction_booking_link);

        $this->actingAs($this->staff)->post(route('course.creation.update'), $this->fields(['id' => $creation->id, 'induction_booking_link' => 'not a link']), ['Accept' => 'application/json'])
            ->assertStatus(422);
    }
}
