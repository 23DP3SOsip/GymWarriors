<?php

namespace App\Http\Controllers;

use App\Models\Trainer;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    public function index()
    {
        return response()->json([
            'trainers' => Trainer::query()->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $trainer = Trainer::create($this->validatedData($request));

        return response()->json([
            'trainer' => $trainer,
        ], 201);
    }

    public function update(Request $request, Trainer $trainer)
    {
        $trainer->update($this->validatedData($request));

        return response()->json([
            'trainer' => $trainer->fresh(),
        ]);
    }

    public function destroy(Trainer $trainer)
    {
        $trainer->delete();

        return response()->json([
            'message' => 'Trainer deleted successfully.',
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'specialty' => ['required', 'string', 'max:150'],
            'experience' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'image' => [
                'required',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! preg_match('~^(https?://\S+|/images/[A-Za-z0-9._/-]+)$~D', $value)) {
                        $fail('The image must be an HTTP(S) URL or a path inside /images.');
                    }
                },
            ],
        ]);
    }
}
