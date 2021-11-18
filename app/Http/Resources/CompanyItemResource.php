<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\CompanyAddress as CompanyAddressResource;
use App\Http\Resources\CompanyMedia as CompanyMediaResource;
use App\Models\Company;
use App\Models\CompanyItemCatalog;
use App\Models\CompanyItemMedia;

class CompanyItemResource extends JsonResource
{

    public function toArray($request)
    {
        $item_catalog = CompanyItemCatalog::where('id', $this->id_item_catalog)->first();

        $item_category = CompanyItemCategory::where('id', $this->id_item_category)->first();

        $company = Company::where('id', $item_catalog['id_company'])->first();

        $media = CompanyItemMedia::where('id_item', $this->id)->where('deleted_at', null)->get();

        return [
            'id' => $this->id,
            'id_item_catalog' => $this->id_item_catalog,
            'item_catalog' => $item_catalog['name'],
            'id_item_category' => $this->id_item_category,
            'item_category' => $item_category['name'],
            'item_category' => $item_category['name_zh'],
            'item_name' => $this->item_name,
            'item_description' => $this->item_description,
            'item_price' => $this->item_price,
            'id_company' => $company['id'],
            'company_name' => $company['name'],
            'media' => $media
        ];
    }

}