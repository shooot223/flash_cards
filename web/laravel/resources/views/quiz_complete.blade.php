@extends('layouts.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/quiz_complete.css') }}"/>
@endpush

@section('title', 'Cramist | 完了')

@section('content')
    <div class="quizForm">
        <section class="completeCard">
            <div class="completeIconWrap">
                <div class="completeIcon">✔</div>
            </div>

            <h1 class="completeTitle">
                {{ $mode === 'update' ? 'クイズを更新しました' : 'クイズを登録しました' }}
            </h1>

            <p class="completeDescription">
                @if($mode === 'update')
                    クイズの更新が正常に完了しました。<br>
                    続けて別の問題を作成することも、マイページに戻ることもできます。
                @else
                    問題の作成が正常に完了しました。<br>
                    続けて新しい問題を作成することも、ホームに戻ることもできます。
                @endif
            </p>

            <div class="completeMessage">
                <div class="completeMessageTitle">
                    {{ $mode === 'update' ? '✅ Cramist の問題を更新しました' : '📚 Cramist に新しい問題が追加されました' }}
                </div>
                <div class="completeMessageText">
                    {{ $mode === 'update'
                        ? '内容が最新の状態に反映されました。次のアクションを選んで進めましょう。'
                        : '学習コンテンツが1つ増えました。次のアクションを選んで進めましょう。'
                    }}
                </div>
            </div>

            <div class="completeActions">
                <a href="{{ route('quiz.create') }}" class="completePrimaryButton">
                    {{ $mode === 'update' ? '新しいクイズを作成する' : 'もう一問作成する' }}
                </a>

                <a href="{{ route('mypage') }}" class="completeSecondaryButton">
                    マイページに戻る
                </a>
            </div>
        </section>
    </div>
@endsection
