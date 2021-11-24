<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\CompanyAddress as CompanyAddressResource;
use App\Http\Resources\CompanyMedia as CompanyMediaResource;

use App\Models\Company;
use App\Models\CompanyItemCatalog;
use App\Models\CompanyItemCategory;
use App\Models\CompanyItemMedia;
use App\Models\CompanyItem;

class CompanyCatalogResource extends JsonResource
{

    public function toArray($request)
    {

        $company = Company::where('id', $this->id_company)->get();

        $item = CompanyItem::where('id_item_catalog', $this->id)->get();

        foreach($item as $key => $value){
            $price[$key] = $value->item_price;
        }

        $starting_price = min($price);

        return [
            'id' => $this->id,
            'id_company' => $this->id_company,
            'name' => $this->name,
            'starting_price' => $starting_price,
            'company' => $company,
            'item' => $item,
        ];
    }

}