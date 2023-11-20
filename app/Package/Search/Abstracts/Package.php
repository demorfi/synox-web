<?php declare(strict_types=1);

namespace App\Package\Search\Abstracts;

use App\Package\Abstracts\Package as PackageAbstract;
use App\Package\Search\Interfaces\Package as SearchPackageInterface;
use App\Package\Search\Filter;
use App\Components\Helper;
use Digua\Components\Client\Curl;
use Digua\Traits\Client;
use DOMWrap\Document;

abstract class Package extends PackageAbstract implements SearchPackageInterface
{
    use Client;

    /**
     * @return Curl
     */
    protected function client(): Curl
    {
        return new Curl;
    }

    /**
     * @param mixed $markup
     * @return Document
     */
    protected function document(mixed $markup = ''): Document
    {
        return Helper::document($markup);
    }

    /**
     * @return Filter
     */
    public function onlyAllowed(): Filter
    {
        return new Filter(Filter::usesCasesCollection()->toArray());
    }
}