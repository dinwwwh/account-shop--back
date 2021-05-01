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
     * check whether user enough silver coin
     *
     * @param int as $coin
     * @return boolean
     */
    public function checkEnoughSilverCoin(int $coin)
    {
        return $this->silver_coin >= $coin;
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

    /**
     * reduce silver coin base $coin
     *
     * @param int $coin
     * @return boolean
     */
    public function reduceSilverCoin(int $coin)
    {
        if (!$this->checkEnoughSilverCoin($coin)) {
            return false;
        }

        return $this->update([
            'silver_coin' => ($this->silver_coin - $coin),
        ]);
    }
}
