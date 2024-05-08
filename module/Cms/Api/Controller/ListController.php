<?php


namespace Module\Cms\Api\Controller;


use ModStart\Core\Exception\BizException;
use ModStart\Core\Input\InputPackage;
use ModStart\Core\Input\Response;
use Module\Cms\Util\CmsContentUtil;
use Module\Cms\Util\CmsMemberPermitUtil;

/**
 * @Api 通用CMS
 */
class ListController extends BaseCatController
{
    /**
     * @Api 栏目-获取内容列表
     * @ApiBodyParam cat string 栏目标识（ID、名称）
     * @ApiBodyParam search.isRecommend boolean 搜索条件，是否推荐
     */
    public function index()
    {
        $input = InputPackage::buildFromInput();
        $catId = $input->getTrimString('cat');
        BizException::throwsIfEmpty('分类为空', $catId);
        $data = parent::setup($catId);
        $page = $input->getPage();
        $cat = $data['cat'];
        $model = $data['model'];

        if (empty($cat['pageSize']) || $cat['pageSize'] < 0) {
            $cat['pageSize'] = 12;
        }
        $pageSize = $input->getPageSize('pageSize', null, null, $cat['pageSize']);
        $searchInput = $input->getAsInput('search');
        $option = [
            'where' => [],
        ];
        $isRecommend = $searchInput->getBoolean('isRecommend');
        if ($isRecommend) {
            $option['where']['isRecommend'] = true;
        }
        $option = CmsContentUtil::buildFilter($option, $model);
        $paginateData = CmsContentUtil::paginateCat($cat['id'], $page, $pageSize, $option);
        CmsContentUtil::mergeRecordsData($paginateData['records'], [
            'canVisit' => CmsMemberPermitUtil::canVisitCat($cat),
        ]);

        $viewData = [];
        $viewData['total'] = $paginateData['total'];
        $viewData['cat'] = $cat;
        $viewData['page'] = $page;
        $viewData['pageSize'] = $pageSize;
        $viewData['records'] = $paginateData['records'];
        return Response::generateSuccessData($viewData);
    }
}
