<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\AuthorizationException;
use App\Exceptions\ClinicException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class Controller
{
    protected function handleExceptions(callable $callback, string $action): mixed
    {
        try {
            return $callback();
        } catch (HttpException $e) {
            throw $e;
        } catch (AuthorizationException $e) {
            throw new HttpException(403, $e->getMessage());
        } catch (ClinicException $e) {
            Log::warning("Error de dominio en {$action}", [
                'user_id' => auth()->id(),
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('toast', [
                'type' => 'error',
                'message' => $e->getMessage() ?: 'Ocurrió un error inesperado. Intente nuevamente.',
            ]);
        } catch (\Throwable $e) {
            Log::error("Error en {$action}", [
                'user_id' => auth()->id(),
                'action' => $action,
                'model' => $e->getTrace()[0]['class'] ?? 'unknown',
                'error' => $e->getMessage(),
                'request' => request()->except('_token'),
            ]);

            return redirect()->back()->with('toast', [
                'type' => 'error',
                'message' => 'Ocurrió un error inesperado. Intente nuevamente.',
            ]);
        }
    }
}
