@extends('layouts.app', ['fullWidth' => true, 'immersive' => true])

@section('content')
    <div class="smart-board-join-page" data-smart-board-join
        data-session='@json($sessionData)'
        data-endpoint="{{ route('smart-board-sessions.show', $smartBoardSession) }}"
        data-response-endpoint="{{ route('smart-board-sessions.responses.store', $smartBoardSession) }}">
        <div class="smart-board-join-card">
            <div class="smart-board-join-logo"><i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i></div>
            <p class="smart-board-eyebrow">سبورة معلم الذكية</p>
            <p class="smart-board-session-code">رمز الجلسة <strong>{{ $smartBoardSession->public_code }}</strong></p>
            <div id="studentPollContent"></div>
            <p class="smart-board-save-status" id="studentPollStatus" role="status">اختر إجابتك</p>
        </div>
    </div>
@endsection
