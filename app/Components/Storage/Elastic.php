<?php declare(strict_types=1);

namespace App\Components\Storage;

use App\Components\Config;
use Digua\Interfaces\Storage;
use Digua\Exceptions\Storage as StorageException;
use Elasticsearch\{ClientBuilder, Client};
use Exception;

class Elastic implements Storage
{
    /**
     * @var ?Client
     */
    private static ?Client $client = null;

    /**
     * @param string $name
     * @throws StorageException
     */
    public function __construct(private readonly string $name)
    {
        $this->connect(new Config('elastic'));
    }

    /**
     * @param Config $config
     * @return void
     * @throws StorageException
     */
    protected function connect(Config $config): void
    {
        try {
            if (!self::$client) {
                $builder = ClientBuilder::create()
                    ->setSSLVerification(false)
                    ->setConnectionParams([
                        'client' => [
                            'timeout'         => $config->get('timeout', 2),
                            'connect_timeout' => $config->get('timeout', 2)
                        ]
                    ]);

                if ($config->get('cloud_id')) {
                    $builder->setElasticCloudId($config->get('cloud_id'))
                        ->setApiKey($config->get('api_id'), $config->get('api_key'));
                } else {
                    $hosts = [];
                    $tok   = strtok($config->get('hosts', 'localhost:9200'), ',');
                    while ($tok !== false) {
                        $hosts[] = $tok;
                        $tok     = strtok(',');
                    }
                    $builder->setHosts($hosts);
                }

                self::$client = $builder->build();
                self::$client->ping();
            }
        } catch (Exception $e) {
            throw new StorageException($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function getPath(): string
    {
        $connection = self::$client->transport->getConnection();
        return sprintf('%s:%d', $connection->getHost(), $connection->getPort());
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $id
     * @return ?array
     */
    public function get(string $id): ?array
    {
        try {
            return self::$client->get(['index' => $this->name, 'id' => $id]) ?: null;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function read(): ?array
    {
        try {
            return self::$client->search(['index' => $this->name]) ?: null;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * @param array $criteria
     * @return ?array
     */
    public function search(array $criteria): ?array
    {
        try {
            $result = self::$client->search(['index' => $this->name, 'body' => ['query' => $criteria]]);
            return $result['hits']['hits'] ?? null;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * @param string $id
     * @param array  $data
     * @return bool
     */
    public function update(string $id, array $data): bool
    {
        $result = self::$client->update(['index' => $this->name, 'id' => $id, 'body' => ['doc' => $data]]);
        return !empty($result) && isset($result['_version']) && $result['_version'] >= 1;
    }

    /**
     * @inheritdoc
     */
    public function write(string|array $data): bool
    {
        $result = self::$client->index(['index' => $this->name, ...$data]);
        return !empty($result) && isset($result['_version']) && $result['_version'] >= 1;
    }

    /**
     * @inheritdoc
     */
    public function rewrite(callable|string|array $data): bool
    {
        $this->free();
        return $this->write(is_callable($data) ? $data($this->read()) : $data);
    }

    /**
     * @inheritdoc
     */
    public function free(): bool
    {
        $result = self::$client->indices()->delete(['index' => $this->name]);
        return !empty($result) && isset($result['acknowledged']) && $result['acknowledged'] === true;
    }

    /**
     * @inheritdoc
     */
    public static function has(string $name): bool
    {
        return self::$client->indices()->exists(['index' => $name]);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasId(string $id): bool
    {
        return self::$client->exists(['index' => $this->name, 'id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public function hasEof(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function setEof(): bool
    {
        return false;
    }
}