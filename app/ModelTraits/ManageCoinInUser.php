<?php

namespace App\ModelTraits;

trait ManageCoinInUser
{
    /**
     * check whether user enough gold coin
     *
     * @param number as $coin
     * @return boolean
     */
    public function checkEnoughGoldCoin($coin)
    {
        return $this->gold_coin <= $coin;
    }

    /**
     * reduce gold coin base $coin
     *
     * @param number as $coin
     * @return boolean
     */
    public function reduceGoldCoin($coin)
    {
        return $this->update([
            'gold_coin' => $this->gold_coin - $coin,
        ]);
    }
}
