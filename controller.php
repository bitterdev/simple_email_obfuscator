<?php

namespace Concrete\Package\SimpleEmailObfuscator;

use Concrete\Core\Html\Service\Html;
use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\View\View;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class Controller extends Package
{
    protected string $pkgHandle = 'simple_email_obfuscator';
    protected string $pkgVersion = '0.0.1';
    protected $appVersionRequired = '9.0.0';

    public function getPackageDescription(): string
    {
        return t('Automatically obfuscates email addresses to protect them from spambots and restores them on page load for real visitors.');
    }

    public function getPackageName(): string
    {
        return t('Simple Email Obfuscator');
    }

    public function on_start()
    {
        /** @var EventDispatcherInterface $eventDispatcher */
        /** @noinspection PhpUnhandledExceptionInspection */
        $eventDispatcher = $this->app->make(EventDispatcherInterface::class);

        $eventDispatcher->addListener('on_before_render', function () {
            $c = Page::getCurrentPage();

            if ($c instanceof Page && !$c->isError() && !$c->isSystemPage() && !$c->isEditMode()) {
                $v = View::getInstance();
                /** @var Html $htmlService */
                $htmlService = $this->app->make(Html::class);
                $v->addHeaderItem($htmlService->javascript("email-obfuscator.js", "simple_email_obfuscator"));
            }
        });

        $eventDispatcher->addListener('on_page_output', function ($event) {
            /** @var $event GenericEvent */
            $htmlCode = $event->getArgument('contents');
            $c = Page::getCurrentPage();

            if ($c instanceof Page && !$c->isError() && !$c->isSystemPage() && !$c->isEditMode()) {
                $htmlCode = preg_replace_callback(
                    '/([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+)/',
                    function ($matches) {
                        $localPart = base64_encode($matches[1]);
                        $domain = base64_encode($matches[2]);

                        return "{$localPart}@{$domain}";
                    },
                    $htmlCode
                );
            }

            $event->setArgument('contents', $htmlCode);
        });
    }
}