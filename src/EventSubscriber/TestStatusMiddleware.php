<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Controller\V1\Game\TestController;
use App\Entity\Test;
use App\Entity\TestAction;
use App\Enum\TestStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

/**
 * Check status of test for controller.
 */
class TestStatusMiddleware implements EventSubscriberInterface
{
    /** @var array Controllers for checking. */
    const TEST_STATUS_IN_PROCESS_CONTROLLERS = [
        [TestController::class, 'attemptAnswer'],
        [TestController::class, 'showAnswer'],
    ];

    private EntityManagerInterface $em;

    private Security $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'checkTestStatus',
        ];
    }

    public function checkTestStatus(ControllerArgumentsEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $controller = $event->getController();

        if (!\is_array($controller) || count($controller) !== 2) {
            return;
        }

        foreach (self::TEST_STATUS_IN_PROCESS_CONTROLLERS as $controllerForCheck) {
            if ($controller[0] instanceof $controllerForCheck[0] && $controller[1] === $controllerForCheck[1]) {
                $args = $event->getArguments();

                foreach ($args as $arg) {
                    if (is_object($arg) && $arg instanceof Test) {
                        $testActionRepository = $this->em->getRepository(TestAction::class);
                        $testStatus = $testActionRepository->getTestStatus($this->security->getUser(), $arg);

                        if ($testStatus !== TestStatus::IN_PROCESS) {
                            throw new BadRequestHttpException('TEST_STATUS_NOT_IN_PROCESS');
                        }
                    }
                }

                return;
            }
        }
    }
}
