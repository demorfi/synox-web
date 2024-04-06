<?php declare(strict_types=1);

namespace App\Components\Packages\Extension;

use App\Controllers\Base as BaseController;
use App\Components\Settings;
use App\Package\{
    Adapter,
    Search\Enums\Category,
    Extension\Abstracts\Package,
    Extension\Enums\Subtype,
    Exceptions\Package as PackageException
};
use Digua\{
    Enums\Headers,
    LateEvent,
    Request,
    RouteDispatcher,
    Response,
    Components\Event,
    Interfaces\Route as RouteInterface,
    Interfaces\Guardian as GuardianInterface,
    Exceptions\Abort as AbortException,
    Exceptions\Storage as StorageException
};
use SynoxWebApi;
use Exception;

class JackettApiHook extends Package
{
    /**
     * @var Subtype
     */
    private Subtype $subtype = Subtype::HOOK;

    /**
     * @var string
     */
    private string $name = 'Jackett Api Hook';

    /**
     * @var string
     */
    private string $description = 'Emulation Jackett API (Indexers)';

    /**
     * @var string
     */
    private string $version = '1.0';

    /**
     * @var array|int[][]
     */
    private static array $categories = [
        Category::VIDEO->value       => [2000],
        Category::AUDIO->value       => [2000],
        Category::APPLICATION->value => [2000],
        Category::GAME->value        => [2000]
    ];

    /**
     * @param Settings $settings
     * @throws StorageException
     */
    public function __construct(private readonly Settings $settings)
    {
        parent::__construct($this->settings);
        $this->addSetting('text', 'profile-id', '', 'Profile ID');
        $this->addSetting('text', 'api-url', '', 'Override Synox Web URL');
    }

    /**
     * @inheritdoc
     */
    public function getSubtype(): Subtype
    {
        return $this->subtype;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(): bool
    {
        return !empty($this->getSetting('profile-id')) && class_exists('SynoxWebApi\\Api');
    }

    /**
     * @inheritdoc
     */
    public function getRequires(): array
    {
        return ['Profile ID', 'Synox Web Api'];
    }

    /**
     * @inheritdoc
     */
    public function wakeup(): void
    {
        LateEvent::subscribe(RouteDispatcher::class, function (Event $event) {
            if (str_starts_with($event->request->getData()->query()->getPath(), '/v2.0/indexers/status:healthy/')) {
                $event->builder->forced(_JackettApi_::class, 'default');
            }
        });
    }

    /**
     * @param Request\Query $request
     * @return array
     * @throws PackageException
     */
    public function search(Request\Query $request): array
    {
        $profileId = $this->getSetting('profile-id');
        if (empty($profileId)) {
            throw new PackageException('Profile ID is required!', Headers::FAILED_DEPENDENCY->value);
        }

        $query = $request->get('query');
        if (empty($query) || strlen($query) <= 3) {
            throw new PackageException('Empty or short search query!', Headers::FAILED_DEPENDENCY->value);
        }

        $host    = rtrim($this->getSetting('api-url', $request->getHost()), '/');
        $link    = $request->getHost() . $request->getUri();
        $results = [];

        try {
            $api = new SynoxWebApi\Api($host . '/api/');
            foreach ($api->search()->create($query, $profileId)->run() as $entry) {
                $item = [
                    'Tracker'      => $entry->getPackage(),
                    'Details'      => $entry->getPageUrl(),
                    'Title'        => $entry->getTitle(),
                    'Size'         => $entry->getSize(),
                    'PublishDate'  => $entry->getDate(),
                    'Category'     => self::$categories[$entry->getCategory()] ?? [0],
                    'CategoryDesc' => $entry->getCategory(),
                    'Seeders'      => (int)$entry->getSeeds(),
                    'Peers'        => (int)$entry->getPeers()
                ];

                if ($entry->isContent()) {
                    $item['MagnetUri'] = $entry->getContent()->getMagnet();
                } else {
                    $fetchInfo    = base64_encode(json_encode(['packageId' => $entry->getId(), 'fetchId' => $entry->getFetchId()]));
                    $item['Link'] = $link . '&fetch=' . $fetchInfo;
                }

                $results[] = $item;
            }
        } catch (Exception $e) {
            throw new PackageException($e->getMessage(), Headers::FAILED_DEPENDENCY->value);
        }

        return $results;
    }

    /**
     * @param Request\Query $request
     * @return string
     * @throws PackageException
     */
    public function result(Request\Query $request): string
    {
        $fetch = @json_decode(base64_decode($request->get('fetch')), true);
        if (empty($fetch) || !is_array($fetch) || !isset($fetch['packageId'], $fetch['fetchId'])) {
            throw new PackageException('Empty or invalid fetch query!', Headers::FAILED_DEPENDENCY->value);
        }

        $host = rtrim($this->getSetting('api-url', $request->getHost()), '/');

        try {
            $api = new SynoxWebApi\Api($host . '/api/');
            return $api->content()->fetch($fetch['packageId'], $fetch['fetchId'])?->download();
        } catch (Exception $e) {
            throw new PackageException($e->getMessage(), Headers::FAILED_DEPENDENCY->value);
        }
    }
}

class _JackettApi_ extends BaseController implements GuardianInterface
{
    /**
     * @var Adapter|JackettApiHook
     */
    private readonly Adapter|JackettApiHook $package;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->package = $this->repository->getPackages()->find('extension-jackettapihook');
    }

    /**
     * @param RouteInterface $route
     * @return bool
     */
    public function granted(RouteInterface $route): bool
    {
        return true;
    }

    /**
     * @return Response
     * @throws AbortException
     */
    public function defaultAction(): Response
    {
        try {
            if ($this->dataRequest()->query()->has('fetch')) {
                return $this->response($this->package->result($this->dataRequest()->query()));
            }
            return $this->response(['Results' => $this->package->search($this->dataRequest()->query())]);
        } catch (PackageException $e) {
            $this->throwAbort($e->getCode(), $e->getMessage());
        }
    }
}