<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Base;
use App\Package\{Update, Search\Filter};
use Digua\{Response, Env};
use Digua\Attributes\Guardian\RequestPathRequired;
use Digua\Enums\Headers;
use Digua\Exceptions\{
    Abort as AbortException,
    Base as BaseException
};

class Packages extends Base
{
    /**
     * @return array
     */
    public function getDefaultAction(): array
    {
        return $this->repository->getPackages()->getAll();
    }

    /**
     * @return array
     */
    public function getFiltersAction(): array
    {
        return Filter::usesCollection()->toArray();
    }

    /**
     * @param string $name
     * @return Response
     * @throws AbortException
     */
    #[RequestPathRequired('name')]
    public function putUpdateAction(string $name): Response
    {
        if (!Env::isDev()) {
            $this->throwAbort(Headers::METHOD_NOT_ALLOWED, 'Updating is allowed only in development mode!');
        }

        try {
            return $this->response(['success' => true, 'state' => Update::add($name)]);
        } catch (BaseException $e) {
            $this->throwAbort($e->getCode() ?: Headers::EXPECTATION_FAILED, $e->getMessage());
        }
    }

    /**
     * @return Response
     * @throws AbortException
     * @uses putUpdateAction
     */
    public function postUploadAction(): Response
    {
        if (!Env::isDev()) {
            $this->throwAbort(Headers::METHOD_NOT_ALLOWED, 'Uploading is allowed only in development mode!');
        }

        try {
            $files = $this->dataRequest()->files();
            if ($files->size() <> 1) {
                $this->throwAbort(Headers::NOT_ACCEPTABLE, 'Only one file can be uploaded at a time!');
            }
            return $this->response(['success' => true, 'name' => Update::upload($files->collection()->first())]);
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
    public function putChangeStateAction(string $id): Response
    {
        try {
            $state = $this->repository->getPackages()->find($id)?->state();
            if (is_null($state)) {
                $this->throwAbort(Headers::UNPROCESSABLE_ENTITY);
            }

            $enabled = $this->dataRequest()->post()->getFixedTypeValue('enabled', 'bool');
            if ($state->get('enabled') == $enabled) {
                $this->throwAbort(Headers::NOT_MODIFIED);
            }

            $state->setStateValue('enabled', $enabled);
            $state->save();
            return $this->response(['success' => true, 'enabled' => $enabled], Headers::ACCEPTED);
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
    public function putUpdateSettingsAction(string $id): Response
    {
        try {
            $package = $this->repository->getPackages()->find($id);
            if (is_null($package)) {
                $this->throwAbort(Headers::UNPROCESSABLE_ENTITY);
            }

            $settings = $this->dataRequest()->post()->collection()->collapse('settings');
            if ($settings->isEmpty()) {
                $this->throwAbort(Headers::BAD_REQUEST);
            }

            foreach ($settings as $key => $value) {
                if (is_scalar($value)) {
                    $package->{$key} = is_array($package->{$key}) ? [...$package->{$key}, 'value' => $value] : $value;
                }
            }

            $package->saveSettings();
            return $this->response(['success' => true, 'state' => $package], Headers::ACCEPTED);
        } catch (BaseException $e) {
            $this->throwAbort($e->getCode() ?: Headers::EXPECTATION_FAILED, $e->getMessage());
        }
    }
}