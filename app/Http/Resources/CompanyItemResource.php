<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\CompanyAddress as CompanyAddressResource;
use App\Http\Resources\CompanyMedia as CompanyMediaResource;
use App\Models\Company;
use App\Models\CompanyItemCategory;
use App\Models\CompanyMedia;

class CompanyItemResource extends JsonResource
{

    public function toArray($request)
    {
        $item_category = CompanyItemCategory::where('id', $this->id_item_category)->first();

        $company = Company::where('id', $item_category['id_company'])->first();

        $media = CompanyMedia::where('id_company_item', $this->id)->get();

        return [
            'id' => $this->id,
            'id_item_category' => $this->id_item_category,
            'item_category' => $item_category['name'],
            'item_name' => $this->item_name,
            'item_description' => $this->item_description,
            'item_price' => $this->item_price,
            'id_company' => $company['id'],
            'company_name' => $company['name'],
            'media' => $media
        ];
    }

}