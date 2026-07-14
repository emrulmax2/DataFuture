<div class="myhr-group-members">
    <div class="myhr-group-members__summary">
        <span>{{ $group->name }}</span>
        <strong>{{ $members->count() }} {{ $members->count() === 1 ? 'member' : 'members' }}</strong>
    </div>

    @forelse($members as $member)
        @php
            $employment = $member->employment;
            $jobTitle = optional(optional($employment)->employeeJobTitle)->name;
            $department = optional(optional($employment)->department)->name;
            $phone = $member->telephone ?: optional($employment)->office_telephone;
            $mobile = $member->mobile ?: optional($employment)->mobile;
            $email = $member->email ?: optional($employment)->email;
            $hasProfilePhoto = filled($member->photo) && \Illuminate\Support\Facades\Storage::disk('local')->exists('public/employees/'.$member->id.'/'.$member->photo);
            $nameParts = preg_split('/\s+/', trim($member->first_name.' '.$member->last_name), -1, PREG_SPLIT_NO_EMPTY);
            $initials = empty($nameParts) ? 'NA' : strtoupper(substr($nameParts[0], 0, 1).substr($nameParts[count($nameParts) - 1], 0, 1));
        @endphp

        <div class="myhr-group-member-row">
            <span class="myhr-group-member-row__avatar">
                @if($hasProfilePhoto)
                    <img src="{{ $member->photo_url }}" alt="{{ $member->full_name }}">
                @else
                    <span>{{ $initials }}</span>
                @endif
            </span>
            <span class="myhr-group-member-row__person">
                <strong>{{ $member->full_name }}</strong>
                <span>{{ $jobTitle ?: 'Position not set' }}</span>
            </span>
            <span class="myhr-group-member-row__meta">
                <small>Department</small>
                <strong>{{ $department ?: '-' }}</strong>
            </span>
            <span class="myhr-group-member-row__meta">
                <small>Phone</small>
                <strong>{{ $phone ?: $mobile ?: '-' }}</strong>
            </span>
            <span class="myhr-group-member-row__meta">
                <small>Email</small>
                <strong>{{ $email ? strtolower($email) : '-' }}</strong>
            </span>
        </div>
    @empty
        <div class="myhr-group-members__empty">
            <i data-lucide="users"></i>
            <p>No members found in this group.</p>
        </div>
    @endforelse
</div>
