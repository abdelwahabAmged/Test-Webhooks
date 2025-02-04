<?php

namespace Murergrej\PalletShipping\Model;

class CompositeWeight implements \ArrayAccess
{
    protected $items = [];
    protected $total = 0;
    protected $baseWeight = 0;

    public function getItems()
    {
        return $this->items;
    }

    public function setBaseWeight($weight)
    {
        $this->total += $weight - $this->baseWeight;
        $this->baseWeight = $weight;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function shift()
    {
        $item = array_shift($this->items);
        $this->total -= $item;
        return $item;
    }

    public function sort()
    {
        sort($this->items);
    }

    public function rsort()
    {
        rsort($this->items);
    }

    public function fillFrom(CompositeWeight $weightContainer, $targetWeight)
    {
        // sort is not being checked, but assume that already sorted by desc
        foreach ($weightContainer->getItems() as $key => $weight) {
            if ($this->getTotal() + $weight <= $targetWeight) {
                $this[] = $weight;
                unset($weightContainer[$key]);
                if ($this->getTotal() == $targetWeight) {
                    break;
                }
            }
        }
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->items);
    }

    public function offsetGet($offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (!is_null($offset) && isset($this->items[$offset])) {
            $this->total += $value - $this->items[$offset];
        } else {
            $this->total += $value;
        }
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        if (isset($this->items[$offset])) {
            $this->total -= $this->items[$offset];
        }
        unset($this->items[$offset]);
    }
}
