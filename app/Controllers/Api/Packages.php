<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Repositories\Packages as Repository;
use App\Package\Filter;
use Digua\{Request, Response};
use Digua\Controllers\Resource as ResourceController;
use Digua\Attributes\Guardian\RequestPathRequired;
use Digua\Enums\Headers;
use Digua\Exceptions\{
    Base as BaseException,
    Abort as AbortException
};

class Packages extends ResourceController
{
    /**
     * @var Repository
     */
    protected Repository $packages;

    /**
     * @inheritdoc
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->packages = Repository::getInstance();
    }

    /**
     * @return array
     */
    public function getDefaultAction(): array
    {
        return $this->packages->getPackages()->getArrayCopy();
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
            $package = $this->packages->getPackages()->find($id);
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
            $package = $this->packages->getPackages()->find($id);
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
                $package->{$key} = $value;
            }

            $package->saveSettings();
            return $this->response(['success' => true], Headers::ACCEPTED);
        } catch (BaseException $e) {
            $this->throwAbort($e->getCode() ?: Headers::EXPECTATION_FAILED, $e->getMessage());
        }
    }
}
