<?php

namespace App\Http\Controllers;

use App\Models\BindPages;
use App\Models\KeysPage;
use App\Models\Language;
use App\Models\LocalizationPages;
use App\Models\UserLocalization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocalizationController extends Controller
{

    public function index()
    {

        $result = [];

        try {
            $this->setUserMenuInSession();
            $user = auth()->user();
            $pageListKeyLanguage = $this->localisationPage('settings_user');

            $idSelected = UserLocalization::where('email', $user->email)->first();
            $id_languages = 1;

            $arrayLanguage = [];
            $language = Language::all();
            if (!is_null($idSelected)) {
                $id_languages = $idSelected->id_languages;
            }

        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $line = $throwable->getLine();
        }
        return view('user.settings.index', compact('language', 'id_languages','pageListKeyLanguage'));;
    }


    public function updateLanguage(Request $request)
    {
        $user = auth()->user();
        $data = $request->all();
        $lang = Language::find($data['language']);
        $userLocalization = UserLocalization::where('email', $user->email)->first();
        if (!is_null($userLocalization)) {
            UserLocalization::where('email', $user->email)->update(['id_languages' => $lang->id]);
        } else {
            UserLocalization::create([
                'email' => $user->email,
                'id_languages' => $lang->id
            ]);
        }
        $this->setUserMenuInSession();
        return redirect()->back()->withSuccess($this->localisationPage('settings_user')['info_language_update']);
    }

    public function setUserMenuInSession()
    {
        $role = Auth::user()->getrolenames();
        if ($role->contains('admin') !== true) {
            session()->put('user_menu', $this->localisationDashBoardByUser());
        }else{
            session()->put('user_menu', $this->localisationDashBoardByUser());
        }
    }

    /**
     * @return array|false
     */
    public function localisationDashBoardByUser()
    {
        $result = false;

        try {
            $allArray = [];
            $user = auth()->user();

            $localizationUser = UserLocalization::where('email', $user->email)->first();
            $id_languages = 1;

            if (!is_null($localizationUser)) {
                $id_languages = $localizationUser->id_languages;
            }

            $page = BindPages::where('name_page', 'menu_user')->first()->id;
            $PagesLocalization = LocalizationPages::where([
                ['id_languages', $id_languages],
                ['id_page', $page]
            ])->get();

            foreach ($PagesLocalization as $item) {
                $allArray[KeysPage::find($item->id_key_page)->name_key] = $item->text;
            }
            $result = $allArray;
        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $line = $throwable->getLine();

        }

        return $result;
    }

    public function localisationDashBoardByAdmin()
    {
        $result = false;

        try {
            $allArray = [];
            $user = auth()->user();

            $localizationUser = UserLocalization::where('email', $user->email)->first();

            $id_languages = 1;
            if (!is_null($localizationUser)) {
                $id_languages = $localizationUser->id_languages;
            }

            $page = BindPages::where('name_page', 'menu_admin')->first()->id;

            $PagesLocalization = LocalizationPages::where([
                ['id_languages', $id_languages],
                ['id_page', $page]
            ])->get();

            foreach ($PagesLocalization as $item) {
                $allArray[KeysPage::find($item->id_key_page)->name_key] = $item->text;
            }
            $result = $allArray;
        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $line = $throwable->getLine();

        }

        return $result;
    }

    public static function localisationPage(string $getPage)
    {

        $result = false;

        try {
            $allArray = [];
            $user = auth()->user();
            $localizationUser = UserLocalization::where('email', $user->email)->first();
            $id_languages = 1;
            if (!is_null($localizationUser)) {
                $id_languages = $localizationUser->id_languages;
            }
            $page = BindPages::where('name_page', $getPage)->first()->id;

            $PagesLocalization = LocalizationPages::where([
                ['id_languages', $id_languages],
                ['id_page', $page]
            ])->get();
            foreach ($PagesLocalization as $item) {
                $allArray[KeysPage::find($item->id_key_page)->name_key] = $item->text;
            }
            $result = $allArray;
        } catch (\Throwable $throwable) {
            $message = $throwable->getMessage();
            $line = $throwable->getLine();
        }
        return $result;
    }


}
