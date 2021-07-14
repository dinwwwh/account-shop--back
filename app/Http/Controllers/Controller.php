<?php

namespace App\Http\Controllers;

use App\Helpers\RequestHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Use as a argument of ->with method to get relationships
     *
     * @var array
     */
    public array $requiredModelRelationships;

    /**
     * It's word used to search query
     *
     * @param \Illuminate\Http\Request $request
     * @var string
     */
    public string $keyword;

    function  __construct(Request $request)
    {
        $this->requiredModelRelationships = RequestHelper::generateRequiredModelRelationships($request);
        $this->keyword = (string)$request->_keyword;
    }
}
