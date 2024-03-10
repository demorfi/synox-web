<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Base;
use App\Components\Storage\Profiles as ProfilesStorage;
use App\Package\Search\Profile as SearchProfile;
use Digua\Response;
use Digua\Attributes\Guardian\RequestPathRequired;
use Digua\Enums\Headers;
use Digua\Exceptions\{Abort as AbortException, Base as BaseException};

class Profiles extends Base
{
    /**
     * @return array
     */
    public function getDefaultAction(): array
    {
        return ProfilesStorage::load()->getAll();
    }

    /**
     * @return Response
     * @throws AbortException
     */
    public function postCreateAction(): Response
    {
        try {
            $request = $this->dataRequest()->post();
            $id      = $request->getFixedTypeValue('id', 'string');
            $profile = $request->getFixedTypeValue('packages', 'collection')
                ->callWrap(static fn($collection) => SearchProfile::create($collection));

            if ($profile->isEmpty()) {
                $this->throwAbort(Headers::BAD_REQUEST, 'Cannot create an empty profile');
            }

            if (($state = ProfilesStorage::load()->add($profile, $id)) === false) {
                $this->throwAbort(message: 'Failed to create profile!');
            }

            return $this->response(['success' => true, 'state' => $state], Headers::ACCEPTED);
        } catch (BaseException $e) {
            $this->throwAbort($e->getCode() ?: Headers::EXPECTATION_FAILED, $e->getMessage());
        }
    }

    /**
     * @param string $id
     * @return Response
     * @throws AbortException
     */
    #[RequestPathRequired('id')]
    public function putProfileAction(string $id): Response
    {
        try {
            $profiles = ProfilesStorage::load();
            if (!$profiles->has($id)) {
                $this->throwAbort(Headers::UNPROCESSABLE_ENTITY);
            }

            $profile = $this->dataRequest()->post()->getFixedTypeValue('packages', 'collection')
                ->callWrap(static fn($collection) => SearchProfile::create($collection));

            if ($profile->isEmpty()) {
                $this->throwAbort(Headers::BAD_REQUEST, 'Cannot update with empty profile');
            }

            if (($state = $profiles->add($profile, $id)) === false) {
                $this->throwAbort(message: 'Failed to update profile!');
            }

            return $this->response(['success' => true, 'state' => $state], Headers::ACCEPTED);
        } catch (BaseException $e) {
            $this->throwAbort($e->getCode() ?: Headers::EXPECTATION_FAILED, $e->getMessage());
        }
    }

    /**
     * @param string $id
     * @return Response
     * @throws AbortException
     */
    #[RequestPathRequired('id')]
    public function deleteProfileAction(string $id): Response
    {
        try {
            $profiles = ProfilesStorage::load();
            if (!$profiles->has($id)) {
                $this->throwAbort(Headers::UNPROCESSABLE_ENTITY);
            }

            return $this->response(['success' => $profiles->remove($id)]);
        } catch (BaseException $e) {
            $this->throwAbort($e->getCode() ?: Headers::EXPECTATION_FAILED, $e->getMessage());
        }
    }
}