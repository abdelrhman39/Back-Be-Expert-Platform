<?php

namespace App\Livewire\Hooks;

use Livewire\ComponentHook;
use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;

/**
 * Livewire v4 extracts <style> tags into CSS modules and injects them via
 * JavaScript after first paint, which causes a flash of unstyled content.
 * This hook preloads those stylesheets into <head> on the initial render
 * so the browser applies them before painting.
 */
class PrefetchComponentStyles extends ComponentHook
{
    public function dehydrate($context): void
    {
        if (! $context->isMounting()) {
            return;
        }

        $name = $this->component->getName();
        $encodedName = str_replace(
            ['.', '::', ':'],
            ['--', '---', '----'],
            $name
        );
        $moduleBase = url(EndpointResolver::prefix());

        if (method_exists($this->component, 'styleModuleSrc')) {
            $path = $this->component->styleModuleSrc();

            if (is_string($path) && is_file($path)) {
                $href = $moduleBase.'/css/'.$encodedName.'.css?v='.crc32((string) filemtime($path));
                SupportScriptsAndAssets::$renderedAssets['lw-style-'.$name] =
                    '<link rel="stylesheet" href="'.e($href).'" data-livewire-style="'.$encodedName.'">';
            }
        }

        if (method_exists($this->component, 'globalStyleModuleSrc')) {
            $path = $this->component->globalStyleModuleSrc();

            if (is_string($path) && is_file($path)) {
                $href = $moduleBase.'/css/'.$encodedName.'.global.css?v='.crc32((string) filemtime($path));
                SupportScriptsAndAssets::$renderedAssets['lw-global-style-'.$name] =
                    '<link rel="stylesheet" href="'.e($href).'" data-livewire-global-style="'.$encodedName.'">';
            }
        }
    }
}
