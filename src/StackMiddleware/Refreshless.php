<?php

declare(strict_types=1);

namespace Drupal\omnipedia_pipeline\StackMiddleware;

use Drupal\refreshless\Service\RefreshlessKillSwitchInterface;
use function str_contains;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * RefreshLess middleware to detect Pipeline requests.
 */
class Refreshless implements HttpKernelInterface {

  /**
   * Constructor; saves dependencies.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   The wrapped HTTP kernel.
   *
   * @param \Drupal\refreshless\Service\RefreshlessKillSwitchInterface $killSwitch
   *   The RefreshLess kill switch service.
   */
  public function __construct(
    protected readonly HttpKernelInterface $httpKernel,
    protected readonly RefreshlessKillSwitchInterface $killSwitch,
  ) {}

  /**
   * Determine whether the request appears to be from legacy Pipeline.
   *
   * @param Request $request
   *   A request object to check.
   *
   * @return bool
   *   True if this request is from legacy Pipeline; false otherwise.
   */
  protected function isLegacyPipeline(Request $request): bool {

    // It hadn't occurred to us to set a custom user agent at the time, so we have to look for the
    return $request->headers->get('User-Agent', '') === 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36';

  }

  /**
   * Determine whether the request appears to be from modern Pipeline.
   *
   * @param Request $request
   *   A request object to check.
   *
   * @return bool
   *   True if this request is from modern Pipeline; false otherwise.
   */
  protected function isModernPipeline(Request $request): bool {

    return str_contains(
      $request->headers->get('User-Agent', ''), 'PipelineBrowserOmnipedia',
    );

  }

  /**
   * Whether the request appears to support RefreshLess.
   *
   * @param Request $request
   *   A request object to check.
   *
   * @return bool
   *   True if modern Pipeline or non-Pipeline; false if legacy Pipeline.
   */
  protected function supportsRefreshless(Request $request): bool {

    // The modern Pipeline user agent takes precedence.
    if ($this->isModernPipeline($request) === true) {
      return true;
    }

    // Legacy Pipeline does not know to listen to RefreshLess events and so is
    // incompatible after the first full load.
    if ($this->isLegacyPipeline($request) === true) {
      return false;
    }

    // Otherwise, the user agent is probably not Pipeline so it should support
    // RefreshLess.
    return true;

  }

  /**
   * {@inheritdoc}
   */
  public function handle(
    Request $request, int $type = self::MAIN_REQUEST, bool $catch = true,
  ): Response {

    if ($type !== self::MAIN_REQUEST) {
      return $this->httpKernel->handle($request, $type, $catch);
    }

    if ($this->supportsRefreshless($request) === false) {
      $this->killSwitch->trigger();
    }

    return $this->httpKernel->handle($request, $type, $catch);

  }

}
