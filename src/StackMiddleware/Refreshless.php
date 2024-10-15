<?php

declare(strict_types=1);

namespace Drupal\omnipedia_pipeline\StackMiddleware;

use Drupal\refreshless_turbo\Service\RefreshlessTurboKillSwitchInterface;
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
   * @param \Drupal\refreshless_turbo\Service\RefreshlessTurboKillSwitchInterface $killSwitch
   *   The RefreshLess Turbo kill switch service.
   */
  public function __construct(
    protected readonly HttpKernelInterface $httpKernel,
    protected readonly RefreshlessTurboKillSwitchInterface $killSwitch,
  ) {}

  /**
   * Determine whether the request appears to be from Pipeline.
   *
   * @param Request $request
   *
   * @return bool
   */
  protected function isPipeline(Request $request): bool {

    return (
      $request->headers->has('User-Agent') && (
        // Modern.
        str_contains(
          $request->headers->get('User-Agent'), 'PipelineBrowserOmnipedia',
        ) ||
        // Legacy before it occurred to us to provide an identifier lolol.
        $request->headers->get('User-Agent') === 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36'
      )
    );

  }

  /**
   * Whether Pipeline supports RefreshLess for this request.
   *
   * @param Request $request
   *
   * @return bool
   */
  protected function supportsRefreshless(Request $request): bool {
    // Not supported yet.
    return false;
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

    if ($this->isPipeline($request) && !$this->supportsRefreshless($request)) {
      $this->killSwitch->trigger();
    }

    return $this->httpKernel->handle($request, $type, $catch);

  }

}
