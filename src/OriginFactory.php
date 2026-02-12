<?php

namespace Spatie\CraftRay;

use Craft;
use craft\helpers\StringHelper;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Frame;
use Spatie\Ray\Origin\DefaultOriginFactory;
use Spatie\Ray\Origin\Origin;
use Spatie\Ray\Ray;
use yii\base\Component;
use yii\base\Event;

class OriginFactory extends DefaultOriginFactory
{
    public function getOrigin(): Origin
    {
        $frame = $this->getFrame();

        return new Origin(
            $frame->file ?? null,
            $frame->lineNumber ?? null,
        );
    }

    protected function getFrame(): ?Frame
    {
        $frames = array_reverse(Backtrace::create()->frames());

        $indexOfRay = $this->search(function (Frame $frame) {
            if ($frame->class === Ray::class) {
                return true;
            }

            if (str_starts_with($frame->file, __DIR__)) {
                return true;
            }

            return false;
        }, $frames);

        if ($indexOfRay === null) {
            return null;
        }

        /** @var Frame|null $foundFrame */
        $originFrame = $frames[$indexOfRay + 1] ?? null;

        if ($originFrame && str_ends_with($originFrame->file, Ray::makePathOsSafe('ray/src/helpers.php'))) {
            $originFrame = $frames[$indexOfRay + 2] ?? null;
        }

        if (is_null($originFrame->class) && $originFrame->method === 'call_user_func') {
            $originFrame = $frames[$indexOfRay + 2] ?? null;
        }

        if (str_starts_with($originFrame->file, Craft::$app->getRuntimePath() . '/compiled_templates')) {
            return $this->replaceCompiledTemplatePathWithOriginalTemplatePath($originFrame);
        }

        if ($originFrame->class === Event::class) {
            return $this->findFrameForEvent($frames);
        }

        return $originFrame;
    }

    /** @param Frame[] $frames */
    protected function findFrameForEvent(array $frames): ?Frame
    {
        $indexOfComponentCall = $this->search(function (Frame $frame) {
            return $frame->class === Component::class;
        }, $frames);

        if ($indexOfComponentCall === null) {
            return null;
        }

        return $frames[$indexOfComponentCall + 1] ?? null;
    }

    private function replaceCompiledTemplatePathWithOriginalTemplatePath(Frame $frame): Frame
    {
        if (! file_exists($frame->file)) {
            return $frame;
        }

        $fileContents = file_get_contents($frame->file);
        $class = trim(StringHelper::between($fileContents, 'class ', 'extends'));

        /** @var \Twig\Template $template */
        $template = new $class(Craft::$app->getView()->createTwig());

        $originalViewPath = $template->getSourceContext()->getPath();

        if (! file_exists($originalViewPath)) {
            return $frame;
        }

        $frame->file = $originalViewPath;
        $frame->lineNumber = 1;

        return $frame;
    }
}
