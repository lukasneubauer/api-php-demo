<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Persisters\LockSessionPersister;
use App\RequestValidators\CalendarRequestValidator;
use App\Responses\CalendarResponse;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController
{
    private CalendarRequestValidator $calendarRequestValidator;

    private CalendarResponse $calendarResponse;

    private ErrorResponse $errorResponse;

    private LockSessionPersister $lockSessionPersister;

    public function __construct(
        CalendarRequestValidator $calendarRequestValidator,
        CalendarResponse $calendarResponse,
        ErrorResponse $errorResponse,
        LockSessionPersister $lockSessionPersister
    ) {
        $this->calendarRequestValidator = $calendarRequestValidator;
        $this->calendarResponse = $calendarResponse;
        $this->errorResponse = $errorResponse;
        $this->lockSessionPersister = $lockSessionPersister;
    }

    /**
     * @Route("/-/calendar", name="calendar")
     */
    public function index(Request $request): Response
    {
        try {
            $this->calendarRequestValidator->validateRequest($request->headers, $request->getMethod());
            return $this->calendarResponse->createResponse();
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
