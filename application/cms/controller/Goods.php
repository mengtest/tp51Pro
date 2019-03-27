<?php

namespace app\cms\Controller;

use app\common\controller\CmsBase;
use app\common\model\Xcategorys;
use app\common\model\Xgoods;
use think\Request;

class Goods extends CmsBase
{
    private $model ;
    private $categoryModel;
    private $page_limit;
    public function __construct()
    {
        parent::__construct();
        $this->model = new Xgoods();
        $this->categoryModel = new Xcategorys();
        $this->page_limit = config('app.CMS_PAGE_SIZE');
    }

    /**
     * 获取文章列表数据
     * @param Request $request
     * @return \think\response\View
     */
    public function index(Request $request){
        $search = $request->param('str_search');
        $SelStatus = $request->param("SelStatus");
        $CatType = $request->param("CatType");
        //获取所有的商品二级分类
        $categoryList = $this->categoryModel->getCategoryList(2);
        $goods = $this->model->getCmsGoodsForPage(1,$this->page_limit,$search,$SelStatus,$CatType);
        $record_num = $this->model->getCmsGoodsCount($search,$SelStatus,$CatType);
        $data = [
            'goods' => $goods,
            'search' => $search,
            'SelStatus' => $SelStatus?$SelStatus:"Up",
            'CatType' => $CatType?$CatType:"0",
            'categoryList' => $categoryList,
            'record_num' => $record_num,
            'page_limit' => $this->page_limit,
        ];
        return view('index',$data);
    }

    /**
     * @param Request $request
     */
    public function ajaxOpForPage(Request $request){
        if ($request->isPost()){
            $curr_page = $request->post('curr_page',1);
            $SelStatus = $request->param("SelStatus");
            $CatType = $request->param("CatType");
            $search = $request->post('str_search');
            $list = $this->model->getCmsGoodsForPage($curr_page,$this->page_limit,$search,$SelStatus,$CatType);
            return showMsg(1,'success',$list);
        }else{
            return showMsg(0,'sorry，请求不合法');
        }

    }
    /**
     * 添加文章
     * @param Request $request
     * @return \think\response\View|void
     */
    public function add(Request $request){
        if($request->isPost()){
            $input = $request->post();
            $opRes = $this->model->addGoods($input);
            return showMsg($opRes['tag'],$opRes['message']);
        }else{
            $categoryList = $this->categoryModel->getCategoryList(2);
            return view('add',['categoryList'=>$categoryList]);
        }
    }

    /**
     * 更新文章数据
     * @param Request $request
     * @param $id 文章ID
     * @return \think\response\View|void
     */
    public function edit(Request $request,$id){
        if ($request->isPost()){
            $opRes = $this->model->updateCmsGoodsData( $request->post());
            return showMsg($opRes['tag'],$opRes['message']);
        }else{
            $good = $this->model->getCmsGoodsByID($id);
            $comments = [];
            $categoryList = $this->categoryModel->getCategoryList(2);
            $data =
                [
                    'good'=>$good,
                    'comments'=> $comments,
                    'categoryList'=>$categoryList
                ];
            return view('edit',$data);
        }
    }

    /**
     * ajax 更改上下架状态
     * @param Request $request
     */
    public function ajaxPutaway(Request $request){
        $opRes = $this->model->updatePutaway( $request->post('goods_id'),$request->post('okStatus'));
        return showMsg($opRes['tag'],$opRes['message']);
    }

    /**
     * @param Request $request
     */
    public function ajaxDelUploadImg(Request $request){
        $opRes = $this->model->delUploadImg( $request->post('upload_img_id'));
        return showMsg($opRes['tag'],$opRes['message']);
    }
}
