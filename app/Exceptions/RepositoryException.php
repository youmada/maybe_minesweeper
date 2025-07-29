<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use RuntimeException;

class RepositoryException extends RuntimeException
{
    /**
     * QueryException を内包した RepositoryException を作成するファクトリ
     *
     * @param  string|null  $context  エラー発生箇所などの任意メッセージ
     */
    public static function fromQueryException(QueryException $e, ?string $context = null): self
    {
        $message = 'リポジトリ処理中にエラーが発生しました';
        if ($context) {
            $message .= "（{$context}）";
        }
        // コードは QueryException の SQLSTATE コードをそのまま渡すこともできます
        $code = (int) $e->getCode();

        return new self($message, $code, $e);
    }
}
