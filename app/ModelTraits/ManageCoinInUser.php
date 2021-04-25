<?php

namespace App\ModelTraits;

trait ManageCoinInUser
{
    /**
     * check whether user enough gold coin
     *
     * @param int as $coin
     * @return boolean
     */
    public function checkEnoughGoldCoin(int $coin)
    {
        return $this->gold_coin >= $coin;
    }

    /**
     * reduce gold coin base $coin
     *
     * @param int $coin
     * @return boolean
     */
    public function reduceGoldCoin(int $coin)
    {
        if (!$this->checkEnoughGoldCoin($coin)) {
            return false;
        }

        return $this->update([
            'gold_coin' => ($this->gold_coin - $coin),
        ]);
    }
}
