<?php

namespace App\Http\Controllers\CRM;

use App\Companies;
use App\Http\Controllers\Controller;
use App\Language;
use View;
use Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Client;
use Request;

class CompaniesController extends Controller
{
    /**
     * @return array
     */
    private function getDataAndPagination()
    {
        $dataOfCompanies = [
            'companies' => Companies::all(),
            'companiesPaginate' => Companies::paginate(10)
        ];

        return $dataOfCompanies;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return View::make('crm.companies.index')->with($this->getDataAndPagination());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $dataWithPluckOfClient = Client::pluck('full_name', 'id');
        return View::make('crm.companies.create', compact('dataWithPluckOfClient'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $allInputs = Input::all();

        $validator = Validator::make($allInputs, Companies::getRules('STORE'));

        if ($validator->fails()) {
            return Redirect::to('companies/create')->with('message_danger', $validator->errors());
        } else {
            if (Companies::insertRow($allInputs)) {
                return Redirect::to('companies')->with('message_success', Language::getMessage('messages.SuccessCompaniesStore'));
            } else {
                return Redirect::back()->with('message_danger', Language::getMessage('messages.ErrorCompaniesStore'));
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $dataOfCompanies = Companies::find($id);

        return View::make('crm.companies.show')
            ->with([
                'companies' => $dataOfCompanies
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $dataOfCompanies = Companies::find($id);
        $dataWithPluckOfClients = Client::pluck('full_name', 'id');

        return View::make('crm.companies.edit')
            ->with([
                'companies' => $dataOfCompanies,
                'clients' => $dataWithPluckOfClients
            ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        $allInputs = Input::all();

        $validator = Validator::make($allInputs, Companies::getRules('STORE'));

        if ($validator->fails()) {
            return Redirect::back()->with('message_danger', $validator->errors());
        } else {
            if (Companies::updateRow($id, $allInputs)) {
                return Redirect::to('companies')->with('message_success', Language::getMessage('messages.SuccessCompaniesUpdate'));
            } else {
                return Redirect::back()->with('message_success', Language::getMessage('messages.ErrorCompaniesUpdate'));
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        $dataOfCompanies = Companies::find($id);
        $countDeals = count($dataOfCompanies->deals()->get());

        if($countDeals > 0 ) {
            return Redirect::back()->with('message_danger', Language::getMessage('messages.firstDeleteDeals'));
        }

        $dataOfCompanies->delete();

        return Redirect::to('companies')->with('message_success', Language::getMessage('messages.SuccessCompaniesDelete'));
    }

    /**
     * @param $id
     * @return mixed
     */
    public function enable($id)
    {
        $dataOfCompanies = Companies::find($id);

        if (Companies::setActive($dataOfCompanies->id, TRUE)) {
            return Redirect::to('companies')->with('message_success', Language::getMessage('messages.SuccessCompaniesActive'));
        } else {
            return Redirect::back()->with('message_danger', Language::getMessage('messages.ErrorCompaniesActive'));
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function disable($id)
    {
        $dataOfCompanies = Companies::find($id);

        if (Companies::setActive($dataOfCompanies->id, FALSE)) {
            return Redirect::to('companies')->with('message_success', Language::getMessage('messages.CompaniesIsNowDeactivated'));
        } else {
            return Redirect::back()->with('message_danger', Language::getMessage('messages.CompaniesIsDeactivated'));
        }
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function search()
    {
        $getValueInput = Request::input('search');
        $findCompaniesByValue = count(Companies::trySearchCompaniesByValue('name', $getValueInput, 10));
        $dataOfCompanies = $this->getDataAndPagination();

        if(!$findCompaniesByValue > 0 ) {
            return redirect('companies')->with('message_danger', Language::getMessage('messages.ThereIsNoCompanies'));
        } else {
            $dataOfCompanies += ['companies_search' => $findCompaniesByValue];
            Redirect::to('companies/search')->with('message_success', 'Find '.$findCompaniesByValue.' companies!');
        }

        return View::make('crm.companies.index')->with($dataOfCompanies);
    }
}
