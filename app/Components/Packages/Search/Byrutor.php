<?php declare(strict_types=1);

namespace App\Components\Packages\Search;

use App\Package\Search\{Filter, Prototype\Torrent, Enums\Category, Item\Torrent as TorrentItem};
use DOMWrap\Document;

class Byrutor extends Torrent
{
    /**
     * @var string
     */
    const SITE_URL = 'https://thebyrut.org';

    /**
     * @inheritdoc
     */
    protected string $name = 'Byrutor';

    /**
     * @inheritdoc
     */
    protected string $description = 'Torrent tracker ' . self::SITE_URL;

    /**
     * @inheritdoc
     */
    protected string $urlSearch = self::SITE_URL . '/index.php?do=search&subaction=search&story={query}&search_start={page}';

    /**
     * @var string
     */
    protected string $urlDownload = self::SITE_URL . '/index.php?do=download&id=%d';

    /**
     * @inheritdoc
     */
    protected int $numFirstPage = 0;

    /**
     * @inheritdoc
     */
    public function onlyAllowed(): Filter
    {
        return new Filter([Category::getFilterId() => [Category::GAME]]);
    }

    /**
     * @inheritdoc
     */
    protected function getCountPagesFound(Document $page): int
    {
        preg_match('/\s?(?P<total>\d+)\s+/', $page->find('.search-page .berrors')->text(), $matches);
        if (!isset($matches['total']) || !(int)trim($matches['total'])) {
            return 0;
        }

        return (int)($page->find('.bottom-page .page-navi a')->last()?->text() ?: 1);
    }

    /**
     * @inheritdoc
     */
    protected function searchItems(Document $page): iterable
    {
        foreach ($page->find('.short_search .short_titles') as $item) {
            $url = $item->find('a')->attr('href');
            if (!empty($url)) {
                yield $url;
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function buildFetchUrl(string $id): string
    {
        return sprintf($this->urlDownload, filter_var($id, FILTER_SANITIZE_NUMBER_INT));
    }

    /**
     * @inheritdoc
     */
    protected function buildItem(string $url, Document $itemPage, Document $rootPage): TorrentItem
    {
        $item = $this->makeItem();

        // Torrent can only be announced
        $fetchId = $itemPage->find('.main_content a.itemdown_games')->attr('href');
        if (str_starts_with($fetchId, 'http')) {
            $item->setFetchId(filter_var($fetchId, FILTER_SANITIZE_NUMBER_INT));
        }

        $item->setPageUrl($url);
        $item->setCategory(Category::GAME);

        $propCategory = trim($itemPage->find('.main_content .game_details .ulgenre span + a')->text());
        if (!empty($propCategory)) {
            $item->addProperty('Tracker category', $propCategory);
        }

        $item->setTitle(trim($itemPage->find('.main_content .game_details .hname h1')->text()));
        $item->setSize(
            str_replace(
                ['КБ', 'МБ', 'ГБ', 'ТБ'],
                ['Kb', 'Mb', 'Gb', 'Tb'],
                $itemPage->find('.main_content .persize_bottom span')->text()
            )
        );

        // Date created torrent
        $timestamp = preg_replace_callback('/.*\s(?P<date>\d+\s+(?P<month>.*)\s+\d{4}),.*/', function ($matches) {
            if (isset($matches['date'], $matches['month'])) {
                $months = ['', 'янв', 'фев', 'мар', 'апр', 'мая', 'июл', 'июн', 'авг', 'сен', 'окт', 'ноя', 'дек'];
                $month  = (string)array_search(mb_strcut($matches['month'], 0, 6), $months);
                if (!empty($month)) {
                    $date = str_replace($matches['month'], $month, $matches['date']);
                    return (string)date_create_from_format('d m Y', $date)?->getTimestamp();
                }
            }
            return '';
        }, $itemPage->find('.main_content .tupd')->text(), 1);

        if (!empty($timestamp)) {
            $item->setDate((int)$timestamp);
        }

        return $item;
    }
}