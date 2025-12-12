<?php
declare(strict_types=1);

namespace App\Infrastructure\Support\Core;

use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

final class CustomException
{
    /**
     * @param string $message
     * @param int $code
     * @param array $data
     */
    public function __construct(
        public string $message,
        public int $code = Response::HTTP_BAD_REQUEST,
        public array $data = [],
    )
    {
        $this->handle();
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        throw new HttpResponseException(response()->json([
            'message'   => $this->message,
            'data'      => $this->data
        ], $this->code));
    }
}
