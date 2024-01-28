<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Base;
use App\Package\Search\Filter;
use Digua\Response;
use Digua\Attributes\Guardian\RequestPathRequired;
use Digua\Enums\Headers;
use Digua\Exceptions\{Abort as AbortException, Base as BaseException};

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
     * @param string $id
     * @return Response
     * @throws AbortException
     */
    #[RequestPathRequired('id')]
    public function putChangeStateAction(string $id): Response
    {
        try {
            $package = $this->repository->getPackages()->find($id);
            if (is_null($package)) {
                $this->throwAbort(Headers::UNPROCESSABLE_ENTITY);
            }

            $enabled = $this->dataRequest()->post()->getFixedTypeValue('enabled', 'bool');
            if ($package->enabled == $enabled) {
                $this->throwAbort(Headers::NOT_MODIFIED);
            }

            $package->enabled = $enabled;
            $package->saveSettings();
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

            $settings = $this->dataRequest()->post()->collection()
                ->collapse('settings')
                ->except('enabled');

            if ($settings->isEmpty()) {
                $this->throwAbort(Headers::BAD_REQUEST);
            }

            foreach ($settings as $key => $value) {
                if (is_scalar($value)) {
                    $package->{$key} = is_array($package->{$key}) ? [...$package->{$key}, 'value' => $value] : $value;
                }
            }

            $package->saveSettings();
            return $this->response(['success' => true], Headers::ACCEPTED);
        } catch (BaseException $e) {
            $this->throwAbort($e->getCode() ?: Headers::EXPECTATION_FAILED, $e->getMessage());
        }
    }
}