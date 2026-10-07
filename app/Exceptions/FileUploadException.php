<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class FileUploadException extends Exception
{
    /**
     * Get the error message for the exception.
     */
    public function report(): bool
    {
        return true;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function render(Request $request)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status'  => false,
                'message' => $this->getMessage(),
            ], 422);
        }

        return back()->withInput()->with('error', $this->getMessage());
    }
}
