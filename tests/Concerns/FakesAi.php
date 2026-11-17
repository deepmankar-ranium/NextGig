<?php

namespace Tests\Concerns;

use App\AI\AiProvider;
use App\AI\FakeAiProvider;

trait FakesAi
{
    protected function fakeAi(): FakeAiProvider
    {
        $fake = new FakeAiProvider();
        $this->app->instance(AiProvider::class, $fake);

        return $fake;
    }
}
