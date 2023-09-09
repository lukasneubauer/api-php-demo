<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\RequestValidationException;
use App\Exceptions\SessionHasNotMatchingClientIdException;
use App\Json\JsonDecoder;
use App\Persisters\CreateCoursePersister;
use App\Persisters\LockSessionPersister;
use App\RequestValidators\CreateCourseRequestValidator;
use App\Responses\CreateCourseResponse;
use App\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreateCourseController
{
    private CreateCoursePersister $createCoursePersister;

    private CreateCourseRequestValidator $createCourseRequestValidator;

    private CreateCourseResponse $createCourseResponse;

    private ErrorResponse $errorResponse;

    private JsonDecoder $jsonDecoder;

    private LockSessionPersister $lockSessionPersister;

    public function __construct(
        CreateCoursePersister $createCoursePersister,
        CreateCourseRequestValidator $createCourseRequestValidator,
        CreateCourseResponse $createCourseResponse,
        ErrorResponse $errorResponse,
        JsonDecoder $jsonDecoder,
        LockSessionPersister $lockSessionPersister
    ) {
        $this->createCoursePersister = $createCoursePersister;
        $this->createCourseRequestValidator = $createCourseRequestValidator;
        $this->createCourseResponse = $createCourseResponse;
        $this->errorResponse = $errorResponse;
        $this->jsonDecoder = $jsonDecoder;
        $this->lockSessionPersister = $lockSessionPersister;
    }

    /**
     * @Route("/-/create-course", name="create-course")
     */
    public function index(Request $request): Response
    {
        try {
            $requestBody = $request->getContent();
            $data = $this->jsonDecoder->decode($requestBody);
            $this->createCourseRequestValidator->validateRequest(
                $request->headers,
                $request->getMethod(),
                $requestBody,
                $data
            );
            $newCourse = $this->createCoursePersister->createCourse($data);
            return $this->createCourseResponse->createResponse($newCourse);
        } catch (RequestValidationException $e) {
            return $this->errorResponse->createResponse($e->getData());
        } catch (SessionHasNotMatchingClientIdException $e) {
            $this->lockSessionPersister->lockSession();
            return $this->errorResponse->createResponse($e->getData());
        }
    }
}
