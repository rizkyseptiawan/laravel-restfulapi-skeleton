<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\ApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Throwable;

class UserController extends ApiController
{
    /**
     * Display a listing of User.
     * @return JsonResponse
     */
    public function index()
    {
        $userQuery = User::query();
        $userQueryResults = QueryBuilder::for($userQuery)
            ->allowedFilters([])
            ->allowedSorts([])
            ->allowedIncludes([])
            ->paginate();
        return $this->generateApiResponse(data: $userQueryResults);
    }

    /**
     * Store a newly created resource of User in storage.
     * @param Request $request Request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255','unique:users'],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        try {
            DB::beginTransaction();

            $dataToStore = $request->only([
                'name',
                'email',
                'password',
            ]);
            $user = User::create($dataToStore);
            DB::commit();
            return $this->generateApiResponse(data: $user, statusCode: 201);
        } catch (Throwable $th) {
            DB::rollBack();
            return $this->generateApiFromException($th);
        }
    }

    /**
     * Display the specified resource of User.
     * @param string $id User id
     * @return JsonResponse
     */
    public function show(string $id)
    {
        $user = User::query();
        $userQueryResult = QueryBuilder::for($user)->findOrFail($id);
        return $this->generateApiResponse(data: $userQueryResult);
    }

    /**
     * Update the specified resource of User in storage.
     * @param Request $request Request
     * @param string $id User id
     * @return JsonResponse
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255','unique:users,email,'.$user->id],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        try {
            DB::beginTransaction();

            $dataToStore = $request->only([
                'name',
                'email',
                'password',
            ]);

            $user->update($dataToStore);
            DB::commit();
            return $this->generateApiResponse(data: $user, statusCode: 200);
        } catch (Throwable $th) {
            DB::rollBack();
            return $this->generateApiFromException($th);
        }
    }

    /**
     * Remove the specified resource of User from storage.
     * @param string $id User id
     * @return JsonResponse
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        try {
            DB::beginTransaction();
            $user->delete();

            DB::commit();
            return $this->generateApiResponse(statusCode: 204);
        } catch (Throwable $th) {
            DB::rollBack();
            return $this->generateApiFromException($th);
        }
    }
}
