<?php

namespace App\Http\Controllers\Api;

class BaseController
{
    /**
     * Build a multilingual message array from a translation key or raw string.
     *
     * If a translation key (e.g. 'api.login_success') is passed it is resolved
     * in all three supported locales. A plain string is returned as-is for every
     * locale.
     *
     * @return array{en: string, ar: string, fr: string}
     */
    private function buildMessage(string $key, array $replace = []): array
    {
        $locales = ['en', 'ar', 'fr'];
        $result  = [];

        foreach ($locales as $locale) {
            $translated = trans($key, $replace, $locale);
            // trans() returns the key itself when no translation is found
            $result[$locale] = ($translated !== $key) ? $translated : $key;
        }

        return $result;
    }

    /**
     * Success response.
     *
     * @return \Illuminate\Http\JsonResponse<array{success: true, message: array{en: string, ar: string, fr: string}, data?: mixed}>
     */
    public function sendResponse($result = [], string $message = 'api.success', int $code = 200, array $replace = [])
    {
        $response = [
            'success' => true,
            'message' => $this->buildMessage($message, $replace),
            'data'    => $result,
        ];

        if (empty($result)) {
            unset($response['data']);
        }

        return response()->json($response, $code);
    }

    /**
     * Error response.
     *
     * @return \Illuminate\Http\JsonResponse<array{success: false, message: array{en: string, ar: string, fr: string}, data?: mixed}>
     */
    public function sendError(string $error, array $errorMessages = [], int $code = 404, array $replace = [])
    {
        $response = [
            'success' => false,
            'message' => $this->buildMessage($error, $replace),
            'data'    => $errorMessages,
        ];

        if (empty($errorMessages)) {
            unset($response['data']);
        }

        return response()->json($response, $code);
    }

    /**
     * Validation error response.
     *
     * @return \Illuminate\Http\JsonResponse<array{success: false, message: array{en: string, ar: string, fr: string}, data?: mixed}>
     */
    public function sendValidationError(array $errorMessages = [])
    {
        $response = [
            'success' => false,
            'message' => $this->buildMessage('api.validation_error'),
            'data'    => $errorMessages,
        ];

        if (empty($errorMessages)) {
            unset($response['data']);
        }

        return response()->json($response, 422);
    }
}
