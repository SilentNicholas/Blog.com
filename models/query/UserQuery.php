<?php

namespace app\models\query;

use app\models\User;
use yii\db\ActiveQuery;

class UserQuery extends ActiveQuery
{
   /* public function active()
    {
        return $this->andWhere(['status' => User::STATUS_ACTIVE]);
    }

    /**
     * @param null $db
     * @return array|User[]
     */
    /**
     * @param null $db
     * @return array|\yii\db\ActiveRecord[]
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @param null $db
     * @return array|null|User
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}