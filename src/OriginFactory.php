<?php

namespace Spatie\CraftRay;

use Craft;
use craft\helpers\StringHelper;
use Spatie\Backtrace\Frame;
use Spatie\Ray\Origin\DefaultOriginFactory;
use Spatie\Ray\Origin\Origin;
use yii\base\Component;
use yii\base\Event;

class OriginFactory extends DefaultOriginFactory
{
    public function getOrigin(): Origin
    {
        $frame = $this->getFrame();

        return new Origin(
            $frame ? $frame->file : null,
            $frame ? $frame->lineNumber : null,
        );
    }

    protected function getFrame(): ?Frame
    {
        $frames = $this->getAllFrames();
        $indexOfRay = $this->getIndexOfRayFrame($frames);

        /** @var Frame|null $originFrame */
        $originFrame = $frames[$indexOfRay] ?? null;

        if (! $originFrame) {
            return null;
        }

        if (is_null($originFrame->class) && $originFrame->method === 'call_user_func') {
            $originFrame = $frames[$indexOfRay + 1] ?? null;
        }

        if (! $originFrame) {
            return null;
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
