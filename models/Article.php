<?php

namespace app\models;

use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "article".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $content
 * @property string $date
 * @property string $image
 * @property int $viewed
 * @property int $user_id
 * @property int $status
 * @property int $category_id
 *
 * @property ArticleTag[] $articleTags
 * @property Comment[] $comments
 */
class Article extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'article';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'content'], 'required'],
            [['title', 'description', 'content'], 'string'],
            [['date'], 'date', 'format'=>'php:Y-m-d'],
            [['date'], 'default', 'value'=>date('Y-m-d')],
            [['title'], 'string', 'max'=>255]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'content' => 'Content',
            'date' => 'Date',
            'image' => 'Image',
            'viewed' => 'Viewed',
            'user_id' => 'User ID',
            'status' => 'Status',
            'category_id' => 'Category ID',
        ];
    }

    /**
     * @param $filename
     * @return bool
     */
    public function saveImage($filename)
    {
        $this->image = $filename;
        return $this->save(false);
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return ($this->image) ? '/uploads/'. $this->image : '/no-image.png';
    }


    /**
     * Delete unnecessary photo
     */
    public function deleteImage()
    {
        $imageUploadModel = new ImageUpload();
        return $imageUploadModel->deleteCurrentImage($this->image);
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        $this->deleteImage();
        return parent::beforeDelete(); // TODO: Change the autogenerated stub
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * @param $category_id
     * @return bool
     */
    public function saveCategory($category_id)
    {
        $category = Category::findOne($category_id);
        if($category != null){
            $this->link('category', $category);
            return true;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])
            ->viaTable('article_tag', ['article_id' => 'id']);
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getSelectedTags()
    {
       $selectedIds =  $this->getTags()->select('id')->asArray()->all();
       return ArrayHelper::getColumn($selectedIds, 'id');
    }

    /**
     * @param $tags
     */
    public function saveTags($tags)
    {
        if(is_array($tags)){
            $this->clearCurrentTags();
            foreach($tags as $tag){
                $tag = Tag::findOne($tag);
                $this->link('tags', $tag);
            }
        }
    }

    /**
     * @return int
     */
    public function clearCurrentTags()
    {
        return ArticleTag::deleteAll(['article_id'=>$this->id]);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getDate()
    {
        return Yii::$app->formatter->asDate($this->date);
    }

    /**
     * @param int $pageSize
     * @return array
     */
    public static function getAll($pageSize = 5)
    {
        // build a DB query to get all articles
        $query = Article::find();

        // get the total number of articles (but do not fetch the article data yet)
        $count = $query->count();

        // create a pagination object with the total count
        $pagination = new Pagination(['totalCount' => $count, 'pageSize'=>$pageSize]);

        // limit the query using the pagination and retrieve the articles
        $articles = $query->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $data['articles'] = $articles;
        $data['pagination'] = $pagination;

        return $data;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPopular()
    {
        return self::find()->orderBy('viewed desc')->limit(3)->all();
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getRecent()
    {
        return self::find()->orderBy('date asc')->limit(4)->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['article_id'=>'id']);
    }

    /**
     * @return bool
     */
    public function saveArticle()
    {
        $this->user_id = Yii::$app->user->id;
        return $this->save();
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getArticleComments()
    {
        return $this->getComments()->where(['status'=>1])->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id'=>'user_id']);
    }

    /**
     * @return bool
     */
    public function viewedCounter()
    {
        $this->viewed += 1;
        return $this->save(false);
    }
}
