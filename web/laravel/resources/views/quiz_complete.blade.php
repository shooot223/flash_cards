@extends('layouts.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/quiz_complete.css') }}"/>
@endpush

@section('title', 'Cramist | 問題登録完了')

@section('content')
    <div class="quizForm">
        <section class="completeCard">
            <div class="completeIconWrap">
                <div class="completeIcon">✔</div>
            </div>

            <h1 class="completeTitle">クイズを登録しました</h1>

            <p class="completeDescription">
                問題の作成が正常に完了しました。<br>
                続けて新しい問題を作成することも、ホームに戻ることもできます。
            </p>

            <div class="completeMessage">
                <div class="completeMessageTitle">📚 Cramist に新しい問題が追加されました</div>
                <div class="completeMessageText">
                    学習コンテンツが1つ増えました。次のアクションを選んで進めましょう。
                </div>
            </div>

            <div class="completeActions">
                <a href="{{ route('quiz.create') }}" class="completePrimaryButton">
                    もう一問作成する
                </a>

                <a href="{{ route('top') }}" class="completeSecondaryButton">
                    ホームへ戻る
                </a>
            </div>
        </section>
    </div>
@endsection
