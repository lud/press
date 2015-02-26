<?php namespace Lud\Press;

use Lud\Utils\ChainableGroup;

class Collection extends \Illuminate\Support\Collection
{

    public function where($key, $value, $_strict = true)
    {
        $values = (array) $value;
        return $this->filter(function($meta) use ($key, $values) {
            if (!isset($meta[$key])) {
                return false;
            }
            if (is_array($meta[$key])) {
                // example : $meta[$key] is a list of tags, here we return true
                // if we find at least one $meta[$key] member in $values.
                return count(array_intersect($meta[$key], $values)) > 0;
            }
            // else meta[key] is scalar
            return in_array($meta[$key], $values);
        });
    }

    public function getPaginator($currentPage)
    {
        return new PressPaginator($this, $this->count(), $currentPage);
    }

    public function getArchive()
    {
        // Here we return all the files grouped by year > month
        $byYear = [];
        foreach ($this->items as $item) {
            $year = $item->date->format('Y');
            $month = $item->date->format('m');
            $byYear[$year][$month][] = $item;
        }
        $byYearCollection = [];
        foreach ($byYear as $year => $byMonth) {
            $byYearCollection[$year] = new static($byMonth);
        }
        return new static($byYearCollection);
    }

    public static function byDateDesc()
    {
        return function(MetaWrapper $fileA, MetaWrapper $fileB) {
            return $fileA->dateTime() < $fileB->dateTime();
        };
    }
}
