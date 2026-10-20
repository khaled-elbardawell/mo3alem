<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\RandomTypingPassageRequest;
use App\Http\Resources\TypingPassageResource;
use App\Models\TypingPassage;
use App\TypingPassageLanguage;

class RandomTypingPassageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(RandomTypingPassageRequest $request): TypingPassageResource
    {
        $language = TypingPassageLanguage::from($request->string('language')->toString());
        $exclude = $request->integer('exclude');

        $passage = TypingPassage::query()
            ->active()
            ->where('language', $language)
            ->when($exclude > 0, fn ($query) => $query->whereKeyNot($exclude))
            ->inRandomOrder()
            ->firstOrFail();

        return TypingPassageResource::make($passage);
    }
}
