<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\Company as CompanyResource;
use App\Http\Resources\CompanyAddress as CompanyAddressResource;
use App\Http\Resources\CompanyItemMediaResource;
use App\Models\Company;
use App\Models\CompanyItemCatalog;
use App\Models\CompanyItemCategory;
use App\Models\CompanyItemMedia;

class CompanyItemResource extends JsonResource
{

    public function toArray($request)
    {
        $item_catalog = CompanyItemCatalog::where('id', $this->id_item_catalog)->first();

        $item_category = CompanyItemCategory::where('id', $this->id_item_category)->first();

        $company = Company::where('id', $item_catalog['id_company'])->first();
        $company_data = new CompanyResource($company);

        $media = CompanyItemMedia::where('id_item', $this->id)->where('deleted_at', null)->get();

        foreach($media as $key => $value){
            $media_data[$key] = new CompanyItemMediaResource($value);
        }

        return [
            'id' => $this->id,
            'id_item_catalog' => $this->id_item_catalog,
            'item_catalog' => $item_catalog['name'],
            'id_item_category' => $this->id_item_category,
            'item_category' => $item_category['name'],
            'item_category_zh' => $item_category['name_zh'],
            'item_name' => $this->item_name,
            'item_description' => $this->item_description,
            'item_price' => $this->item_price,
            'promoted' => $this->promoted,
            'company' => $company_data,
            'media' => $media_data
        ];
    }

}