<?php

namespace App\Services;

use App\Models\SmartBoardPollResponse;
use App\Models\SmartBoardSession;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SmartBoardSessionService
{
    public function __construct(private MetricService $metrics) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{session: SmartBoardSession, ownerToken: string}
     */
    public function create(?User $user, array $data): array
    {
        $ownerToken = Str::random(64);
        $session = SmartBoardSession::query()->create([
            'smart_board_id' => $data['smart_board_id'] ?? null,
            'user_id' => $user?->id,
            'public_code' => $this->uniquePublicCode(),
            'owner_token_hash' => hash('sha256', $ownerToken),
            'poll_key' => $data['poll_key'] ?? null,
            'poll_snapshot' => $data['poll'] ?? null,
            'expires_at' => now()->addHours(2),
        ]);

        $this->metrics->increment('smart_board_sessions');

        return ['session' => $session, 'ownerToken' => $ownerToken];
    }

    /** @param array<string, mixed> $data */
    public function update(Request $request, SmartBoardSession $session, array $data): SmartBoardSession
    {
        $this->authorizeOwner($request, $session);

        if ($session->expires_at->isPast()) {
            throw ValidationException::withMessages(['session' => 'انتهت صلاحية الجلسة.']);
        }

        $session->forceFill([
            'poll_key' => Arr::get($data, 'poll_key', $session->poll_key),
            'poll_snapshot' => array_key_exists('poll', $data) ? $data['poll'] : $session->poll_snapshot,
            'status' => $data['status'] ?? $session->status,
            'version' => $session->version + 1,
        ])->save();

        return $session->refresh();
    }

    public function close(Request $request, SmartBoardSession $session): void
    {
        $this->authorizeOwner($request, $session);
        $session->forceFill(['status' => 'closed', 'version' => $session->version + 1])->save();
    }

    /** @return array<string, mixed> */
    public function publicPayload(SmartBoardSession $session): array
    {
        $counts = $session->poll_key
            ? $session->responses()
                ->where('poll_key', $session->poll_key)
                ->selectRaw('option_key, COUNT(*) as aggregate')
                ->groupBy('option_key')
                ->pluck('aggregate', 'option_key')
            : collect();

        $options = collect($session->poll_snapshot['options'] ?? [])->map(function (array $option) use ($counts): array {
            return [...$option, 'count' => (int) $counts->get($option['key'], 0)];
        })->values()->all();

        return [
            'code' => $session->public_code,
            'status' => $session->isAvailable() ? 'active' : 'closed',
            'poll_key' => $session->poll_key,
            'poll' => $session->poll_snapshot ? [
                'question' => $session->poll_snapshot['question'],
                'options' => $options,
            ] : null,
            'version' => $session->version,
            'expires_at' => $session->expires_at,
        ];
    }

    /** @param array{poll_key: string, option_key: string, participant_token: string} $data */
    public function respond(SmartBoardSession $session, array $data): SmartBoardPollResponse
    {
        if (! $session->isAvailable() || $session->poll_key !== $data['poll_key']) {
            throw ValidationException::withMessages(['session' => 'الاستطلاع غير متاح الآن.']);
        }

        $optionKeys = collect($session->poll_snapshot['options'] ?? [])->pluck('key');

        if (! $optionKeys->containsStrict($data['option_key'])) {
            throw ValidationException::withMessages(['option_key' => 'الخيار المحدد غير صالح.']);
        }

        $participantHash = hash_hmac('sha256', $data['participant_token'], (string) config('app.key'));
        $response = SmartBoardPollResponse::query()->updateOrCreate(
            [
                'smart_board_session_id' => $session->id,
                'poll_key' => $session->poll_key,
                'participant_hash' => $participantHash,
            ],
            ['option_key' => $data['option_key']],
        );

        $session->increment('version');

        if ($response->wasRecentlyCreated) {
            $this->metrics->increment('smart_board_responses');
        }

        return $response;
    }

    private function authorizeOwner(Request $request, SmartBoardSession $session): void
    {
        $ownsAuthenticatedSession = $request->user()
            && $session->user_id
            && $request->user()->id === $session->user_id;
        $providedToken = (string) $request->header('X-Smart-Board-Token');
        $ownsByToken = $providedToken !== ''
            && hash_equals($session->owner_token_hash, hash('sha256', $providedToken));

        if (! $ownsAuthenticatedSession && ! $ownsByToken) {
            throw new AuthorizationException;
        }
    }

    private function uniquePublicCode(): string
    {
        do {
            $code = collect(str_split('23456789ABCDEFGHJKLMNPQRSTUVWXYZ'))
                ->random(6)
                ->implode('');
        } while (SmartBoardSession::query()->where('public_code', $code)->exists());

        return $code;
    }
}
