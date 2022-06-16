<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function showAll(): JsonResponse
    {
        return response()->json([
            'message' => 'success',
            'status' => 1,
            'items' => User::all()  // paginate(1)
        ]);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function find($id): JsonResponse
    {
        try {
            $data = User::query()->find($id);
//            $data = User::query()->findOrFail($id);

            return response()->json([
                'message' => 'success',
                'user' => $data,
                'status' => 1
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 0
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $validator = \validator($request->all(), [
                'firstname' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'gender' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(
                    $validator->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $inputs = $request->except('password');
            $inputs['password'] = bcrypt($request->password);
            $obj = new User();
            $obj->fill($inputs);
            $obj->save();

            /**
             * Another Version
             */
            /* User::query()->fill($request->all());
            User::query()->create([
                'firstname' => $request->input('firstname'),
                'lastname' => $request->input('lastname'),
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password')),
                'gender' => $request->input('gender')
            ]); */

            /**
             * Other Version
             */
            /* $user = new User();
            $user->firstname = $request->input('firstname');
            $user->email = $request->input('email');
            $user->password = bcrypt($request->input('password'));
            $user->gender = $request->input('gender');
            $user->save(); */

            return response()->json([
                'message' => 'success',
                'status' => 1
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 0
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::query()->find($id);
//            $user = User::query()->findOrFail($id);

            $inputs = $request->except('password');
            $inputs['password'] = bcrypt($request->password);
            $user->fill($inputs);
            $user->update();

            return response()->json([
                'message' => 'success',
                'status' => 1
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 0
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        try {
            User::query()->findOrFail($id)->delete();
            return response()->json(['message' => 'successfully deleted']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
