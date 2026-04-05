<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * confidences テーブルに初期データがなければ投入するマイグレーション
 *
 * 本番環境でシーダーが実行されておらず、confidences テーブルが空のために
 * Answer レコード作成時に NOT NULL 制約違反 (500エラー) が発生していた問題を修正する。
 * マイグレーションで対応することで、デプロイ時に自動的にデータが入る。
 */
return new class extends Migration
{
    public function up(): void
    {
        $levels = ['high', 'medium', 'low'];

        foreach ($levels as $level) {
            // 既に存在する場合はスキップ
            if (!DB::table('confidences')->where('confidence_level', $level)->exists()) {
                DB::table('confidences')->insert([
                    'confidence_level' => $level,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // ロールバック時にも削除しない（他のテーブルから参照されている可能性があるため）
    }
};
