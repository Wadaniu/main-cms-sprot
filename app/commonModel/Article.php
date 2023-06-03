<?php
/**
 * @copyright Copyright (c) 2021 勾股工作室
 * @license https://opensource.org/licenses/Apache-2.0
 * @link https://www.gougucms.com
 */
namespace app\commonModel;
use think\facade\Db;
use think\model;

class Article extends Model
{
    public function articleKeywords()
    {
        return $this->belongsToMany(\app\commonModel\Keywords::class, \app\commonModel\ArticleKeywords::class,'keywords_id','aid');
    }
	
	public static $Type = ['普通','精华','热门','推荐'];
	
    //插入关键字
    public function insertKeyword($keywordArray = [], $aid = 0)
    {
        $insert = [];
        $time = time();
        foreach ($keywordArray as $key => $value) {
            if (!$value) {
                continue;
            }
            $keywords_id = (new Keywords())->increase($value);
            $insert[] = ['aid' => $aid,
                'keywords_id' => $keywords_id,
                'create_time' => $time,
            ];
        }
        $res = Db::name('ArticleKeywords')->strict(false)->field(true)->insertAll($insert);
    }
    /**
    * 获取分页列表
    * @param $where
    * @param $param
    */
    public function getArticleList($where, $param)
    {
		$rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
		$order = empty($param['order']) ? 'a.id desc' : $param['order'];
        $list = self::where($where)
		->field('a.*,c.id as cate_id,c.title as cate_title,u.nickname as admin_name')
        ->alias('a')
        ->join('ArticleCate c', 'a.cate_id = c.id')
        ->join('Admin u', 'a.admin_id = u.id')
		->order($order)
		->paginate($rows, false, ['query' => $param])
		->each(function ($item, $key) {
			$type = (int)$item->type;
			$item->type_str = self::$Type[$type];
		});
		return $list;
    }



    /**
     * 获取分页数据
     *
     * **/
    public function getArticleDatalist($where, $param){
        $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
        $order = empty($param['order']) ? 'id desc' : $param['order'];
        $list = self::where($where)
            ->order($order)
            ->paginate($rows, false, ['query' => $param])
        ;

        $data = $list->toArray();
        //$data['render'] = $list->render();
        return $data;
    }

    /**
    * 添加数据
    * @param $param
    */
    public function addArticle($param)
    {
		$insertId = 0;
        try {
			$param['create_time'] = time();
			$insertId = $this->strict(false)->field(true)->insertGetId($param);
			//关联关键字
			if (isset($param['keyword_names']) && $param['keyword_names']) {
				$keywordArray = explode(',', $param['keyword_names']);
				$res_keyword = $this->insertKeyword($keywordArray,$insertId);
			}
			add_log('add', $insertId, $param);
        } catch(\Exception $e) {
			return to_assign(1, '操作失败，原因：'.$e->getMessage());
        }
		return to_assign(0,'操作成功',['aid'=>$insertId]);
    }

    /**
    * 编辑信息
    * @param $param
    */
    public function editArticle($param)
    {
        try {
            $param['update_time'] = time();
            $this->where('id', $param['id'])->strict(false)->field(true)->update($param);
			//关联关键字
			if (isset($param['keyword_names']) && $param['keyword_names']) {
				\think\facade\Db::name('ArticleKeywords')->where(['aid'=>$param['id']])->delete();
				$keywordArray = explode(',', $param['keyword_names']);
				$res_keyword = $this->insertKeyword($keywordArray,$param['id']);
			}
			add_log('edit', $param['id'], $param);
        } catch(\Exception $e) {
			return to_assign(1, '操作失败，原因：'.$e->getMessage());
        }
		return to_assign();
    }
	

    /**
    * 根据id获取信息
    * @param $id
    */
    public function getArticleById($id)
    {
        $info = $this->where('id', $id)->find();
		return $info;
    }

    /**
    * 删除信息
    * @param $id
    * @return array
    */
    public function delArticleById($id,$type=0)
    {
		if($type==0){
			//逻辑删除
			try {
				$param['delete_time'] = time();
				$this->where('id', $id)->update(['delete_time'=>time()]);
				add_log('delete', $id);
			} catch(\Exception $e) {
				return to_assign(1, '操作失败，原因：'.$e->getMessage());
			}
		}
		else{
			//物理删除
			try {
				$this->where('id', $id)->delete();
				add_log('delete', $id);
			} catch(\Exception $e) {
				return to_assign(1, '操作失败，原因：'.$e->getMessage());
			}
		}
		return to_assign();
    }

    /**
     * 分页列表
     * @param $type 0足球，1篮球
     * @param $where
     * @param $param
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getListByCompId($compType,$where, $param)
    {
        $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
        $order = empty($param['order']) ? 'a.id desc' : $param['order'];
        $list = self::where($where)->where('delete_time',0)->alias('a')
            ->field('a.id,a.cate_id,a.competition_id,a.title,a.desc,a.origin_url,a.read,a.create_time')
            ->order($order)
            ->paginate($rows, false, ['query' => $param])
            ->each(function ($item, $key)use($compType) {
                $item->articleKeywords;
                $type = (int)$item->type;
                $item->type_str = self::$Type[$type];

                if ($compType == 0){
                    $item->sphere_type = 'zuqiu';
                    $comp = (new FootballCompetition())->getShortNameZh($item->competition_id);
                    $item->short_name_py = $comp['short_name_py'];
                }else{
                    $item->sphere_type = 'lanqiu';
                    $comp = (new BasketballCompetition())->getShortNameZh($item->competition_id);
                    $item->short_name_py = $comp['short_name_py'];
                }

            })->toArray();
        return $list;
    }

    public function getFirstArticle($id)
    {
        return self::field('id,title')->where('id','<',$id)->order('id desc')->limit(1)->findOrEmpty();
    }

    public function getNextArticle($id)
    {
        return self::field('id,title')->where('id','>',$id)->order('id asc')->limit(1)->findOrEmpty();
    }

    public function getByKeyword($where,$keyword): array
    {
        $aids = Db::name('article_keywords')
                ->field('aid')
                ->where($where)
                ->where('keywords_id',$keyword)
                ->where('status',1)
                ->order('id','desc')
                ->limit(2)->select()->toArray();

        if (empty($aids)){
            return [];
        }

        return self::field('id,cate_id,competition_id,title,desc,origin_url,read,create_time')
            ->where('id','IN',array_column($aids,'aid'))
            ->select()->toArray();
    }

    public function getRand()
    {
        return Db::query("SELECT `id`,`cate_id`,`competition_id`,`title`,`desc`,`origin_url`,`read`,`create_time` FROM `fb_article` AS a1 
                          WHERE a1.id >= (SELECT ROUND(RAND() * (SELECT MAX(id) FROM `fb_article` )) AS id ) AND a1.delete_time = 0 ORDER BY a1.id ASC LIMIT 2");
    }



    function getArticleCompetition($id){
        $info = $this->getArticleById($id);
        switch ($info->cate_id){
            case 1:
                $competition = FootballCompetition::where("id",$info->competition_id)->find();
                break;
            case 2:
                $competition = BasketballCompetition::where("id",$info->competition_id)->find();
                break;
        }
        if(!$competition){
            return [];
        }
        return $competition->toArray();
    }
}

