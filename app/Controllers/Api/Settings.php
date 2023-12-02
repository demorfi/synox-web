<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Base;
use App\Components\Helper;
use Digua\Response;
use Digua\Attributes\Guardian\RequestPathRequired;
use Digua\Enums\Headers;
use Digua\Exceptions\{
    Base as BaseException,
    Path as PathException,
    Abort as AbortException,
    Storage as StorageException
};

class Settings extends Base
{
    /**
     * @var array|string[]
     */
    private static array $types = ['app'];

    /**
     * @return array
     * @throws PathException
     * @throws StorageException
     */
    public function getDefaultAction(): array
    {
        $settings = [];
        foreach (self::$types as $type) {
            $settings[$type] = Helper::config($type)->getAll();
        }
        return $settings;
    }

    /**
     * @param string $type
     * @return Response
     * @throws AbortException
     */
    #[RequestPathRequired('type')]
    public function putUpdateAction(string $type): Response
    {
        try {
            if (!in_array($type, self::$types)) {
                $this->throwAbort(Headers::UNPROCESSABLE_ENTITY);
            }

            $config = Helper::config($type);
            [$name, $value] = $this->dataRequest()->post()->collection()->only('name', 'value')->getValues();
            if (empty($name) || !$config->has($name)) {
                $this->throwAbort(Headers::UNPROCESSABLE_ENTITY);
            }

            if ($value !== null && $config->update($name, $value)) {
                return $this->response(['success' => true, $name => $value], Headers::ACCEPTED);
            }

            $this->throwAbort(message: 'Failed to update settings');
        } catch (BaseException $e) {
            $this->throwAbort($e->getCode() ?: Headers::EXPECTATION_FAILED, $e->getMessage());
        }
    }
}