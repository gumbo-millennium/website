<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UnderflowException;

/**
 * Handles receiving, storing and publishing of the plazacam.
 * Requires a valid user with 'member' status.
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class PlazaCamController extends Controller
{
    /**
     * Path of the plaza cam
     */
    private const IMAGE_PLAZA = 'plazacam/image-plaza.jpg';

    /**
     * Path of the coffee cam
     */
    private const IMAGE_COFFEE = 'plazacam/image-coffee.jpg';

    /**
     * Prevent the images from being updated on weekends and between 22.00 - 07.00.
     * @return bool
     */
    protected static function isAvailable(): bool
    {
        $time = now();
        $hour = $time->hour;

        return $time->isWeekday() && ($hour >= 7 && $hour < 22);
    }

    /**
     * Returns the path to the file of the given cam.
     * @param string $name
     * @return null|string
     * @throws InvalidArgumentException
     * @throws UnderflowException
     */
    public static function getPlazacamPath(string $name): ?string
    {
        // Get proper cam
        switch ($name) {
            case 'plaza':
                $image = self::IMAGE_PLAZA;
                break;
            case 'coffee':
                $image = self::IMAGE_COFFEE;
                break;
            default:
                $image = null;
        }

        // Throw unknown exception
        if (!$image) {
            throw new InvalidArgumentException('Cam not recognized');
        }

        // Throw 404 if the image is unavailable
        if (!Storage::exists($image)) {
            throw new UnderflowException("Cannot locate cam file");
        }

        // Return path
        return $image;
    }

    /**
     * Gets an image from the web endpoint
     * @param string $location Location to retrieve
     * @return Response
     * @throws BadRequestHttpException
     */
    public function image(string $image)
    {
        // Only allow certain cams
        if (!in_array($image, ['plaza', 'coffee'])) {
            throw new NotFoundHttpException('Cannot find an image for that location.');
        }

        // Show image
        return $this->getImage($image);
    }

    /**
     * Responds with images from API calls
     * @param Request $request
     * @return Response
     * @throws BadRequestHttpException if something is wrong
     */
    public function api(User $user, string $image)
    {
        // Check permission
        if (!$user->hasPermissionTo('plazacam-view')) {
            throw new AccessDeniedHttpException('You are not allowed to view the plazacam');
        }

        // Only allow certain cams
        if (!in_array($image, ['plaza', 'coffee'])) {
            throw new NotFoundHttpException('Cannot find an image for that location.');
        }

        // Show image
        return $this->getImage($image);
    }

    /**
     * Stores images of the plaza- and coffeecam. Requires a user that's a member
     * @param Request $request
     * @param User $user Issuing user
     * @param string $image Image location
     * @return Response
     * @throws AccessDeniedHttpException If user is not a member
     * @throws NotFoundHttpException  If the image location could not be found
     * @throws BadRequestHttpException If the request is not meeting demands
     */
    public function store(Request $request, User $user, string $image)
    {
        // Check permission
        if (!$user->hasPermissionTo('plazacam-update')) {
            throw new AccessDeniedHttpException('You are not allowed to write new plazacam images');
        }

        // Only allow certain cams
        if (!in_array($image, ['plaza', 'coffee'])) {
            throw new NotFoundHttpException('Cannot find an image for that location.');
        }

        // Make sure file is present
        if (!$request->hasFile('file')) {
            throw new BadRequestHttpException('Expected a file on [file], but none was found');
        }

        // Make sure the file is valid
        $file = $request->file('file');

        // Make sure the image is a jpg
        $fileMime = $file->getMimeType();
        if ($fileMime !== 'image/jpeg') {
            throw new BadRequestHttpException("Expected a JPEG image, got [{$fileMime}].");
        }

        // Get file path
        $storedPath = $image === 'plaza' ? self::IMAGE_PLAZA : self::IMAGE_COFFEE;

        // Stored
        $file->storeAs(dirname($storedPath), basename($storedPath));

        // Return empty content with "205 Reset Content" code
        return response()->noContent(Response::HTTP_RESET_CONTENT);
    }

    /**
     * Actually retrieves images
     * @param string $image
     * @return Response
     */
    protected function getImage(string $name)
    {
        try {
            $path = self::getPlazacamPath($name);

            // Get expiration
            $expiresAt = now()->addMinutes(5);

            // Send file, with an expiration of 5 minutes or the first monday at 07.00.
            return Storage::response($path, "{$name}.jpg", [
                'Expire' => $expiresAt->toRfc7231String(),
                'Cache-Control' => sprintf('private, max-age=%d', $expiresAt->diffInSeconds(now()))
            ]);
        } catch (InvalidArgumentException $exception) {
            throw new NotFoundHttpException('Deze webcam is niet beschikbaar', $exception);
        } catch (UnderflowException $exception) {
            throw new NotFoundHttpException('Deze webcam is tijdelijk niet beschikbaar', $exception);
        }
    }
}
