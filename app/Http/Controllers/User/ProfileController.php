<?php

    namespace App\Http\Controllers\User;

    use App\DataTransferObjects\UserDTO;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\Profile\ShowProfileRequest;
    use App\Http\Requests\Profile\UpdateProfileRequest;
    use Illuminate\Contracts\View\Factory;
    use Illuminate\Contracts\View\View;

    class ProfileController extends Controller {

        public function index(): Factory|View {
            // Return a view or JSON
            return view('user.profile.settings');
        }

        public function show(ShowProfileRequest $request): Factory|View {
            $user = new UserDTO($request->user());
            return view('profile.show', [
                'user' => $user,
            ]);
        }

        public function update(UpdateProfileRequest $request): Factory|View {
            dd($request->all());
        }

	    public function edit() {}

	    public function updatePassword() {}

	    public function destroy() {}
    }
