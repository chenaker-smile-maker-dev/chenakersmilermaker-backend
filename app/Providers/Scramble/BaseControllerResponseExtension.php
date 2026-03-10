<?php

namespace App\Providers\Scramble;

use App\Http\Controllers\Api\BaseController;
use Dedoc\Scramble\Infer\Extensions\Event\MethodCallEvent;
use Dedoc\Scramble\Infer\Extensions\MethodReturnTypeExtension;
use Dedoc\Scramble\Support\Type\ArrayItemType_;
use Dedoc\Scramble\Support\Type\Generic;
use Dedoc\Scramble\Support\Type\KeyedArrayType;
use Dedoc\Scramble\Support\Type\Literal\LiteralBooleanType;
use Dedoc\Scramble\Support\Type\Literal\LiteralIntegerType;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\StringType;
use Dedoc\Scramble\Support\Type\Type;
use Dedoc\Scramble\Support\Type\UnknownType;
use Illuminate\Http\JsonResponse;

/**
 * Teaches Scramble how to infer the response type of BaseController helpers so
 * the generated docs correctly show:
 *
 *  {
 *    "success": true|false,
 *    "message": { "en": "...", "ar": "...", "fr": "..." },
 *    "data": <actual shape inferred from the passed argument>
 *  }
 *
 * Scramble's ResponseTypeToSchema requires Generic<JsonResponse, [bodyType, statusCodeType]>
 * (at least 2 template types) to produce a proper OpenAPI response schema.
 */
class BaseControllerResponseExtension implements MethodReturnTypeExtension
{
    public function shouldHandle(ObjectType $type): bool
    {
        return $type->isInstanceOf(BaseController::class);
    }

    public function getMethodReturnType(MethodCallEvent $event): ?Type
    {
        return match ($event->name) {
            'sendResponse' => $this->buildResponse(
                success: true,
                dataType: $event->getArg('result', 0),
                statusCode: $event->getArg('code', 2, new LiteralIntegerType(200)),
            ),
            'sendError' => $this->buildResponse(
                success: false,
                dataType: $event->getArg('errorMessages', 1, new UnknownType),
                statusCode: $event->getArg('code', 2, new LiteralIntegerType(404)),
            ),
            'sendValidationError' => $this->buildResponse(
                success: false,
                dataType: $event->getArg('errorMessages', 0, new UnknownType),
                statusCode: new LiteralIntegerType(422),
            ),
            default => null,
        };
    }

    private function messageType(): KeyedArrayType
    {
        return new KeyedArrayType([
            new ArrayItemType_('en', new StringType),
            new ArrayItemType_('ar', new StringType),
            new ArrayItemType_('fr', new StringType),
        ]);
    }

    private function buildResponse(bool $success, Type $dataType, Type $statusCode): Generic
    {
        $hasData = ! ($dataType instanceof UnknownType);

        $items = [
            new ArrayItemType_('success', new LiteralBooleanType($success)),
            new ArrayItemType_('message', $this->messageType()),
        ];

        if ($hasData) {
            $items[] = new ArrayItemType_('data', $dataType, isOptional: true);
        }

        return new Generic(JsonResponse::class, [
            new KeyedArrayType($items),
            $statusCode,
        ]);
    }
}
