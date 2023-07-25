<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

#Sample Repository Design Pattern

## Giới thiệu
Đi qua một chút về lí thuyết, để có 1 cái nhìn tổng quan hơn về Repository đã nhé

- Repository Pattern là lớp trung gian giữa tầng Business Logic và Data Access, giúp cho việc truy cập dữ liệu chặt chẽ và bảo mật hơn.

- Repository đóng vai trò là một lớp kết nối giữa tầng Business và Model của ứng dụng.

Thông thường thì các phần truy xuất, giao tiếp với database năm rải rác ở trong code, khi bạn muốn thực hiện một thao tác lên database thì phải tìm trong code cũng như tìm các thuộc tính trong bảng để xử lý. Điều này gây lãng phí thời gian và công sức rất nhiều.

Với Repository design pattern, thì việc thay đổi ở code sẽ không ảnh hưởng quá nhiều công sức chúng ra chỉnh sửa.

Một số lý do chung ta nên sử dụng Repository Pattern:

- Một nơi duy nhất để thay đổi quyền truy cập dữ liệu cũng như xử lý dữ liệu.
- Một nơi duy nhất chịu trách nhiệm cho việc mapping các bảng vào object.
- Tăng tính bảo mật và rõ ràng cho code.

Rất dễ dàng để thay thế một Repository với một implementation giả cho việc testing, vì vậy bạn không cần chuẩn bị một cơ sở dữ liệu có sẵn.

## Thiết kế riêng lẻ từng repository cho từng Model

Bạn hãy xem 1 đoạn code viết theo phong cách hiện tại

```
/**
 * @param $id
 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
 */
public function edit($id)
{
    $user = User::finOrFail($id);

    return view('admin.user.edit', compact('user'));
}
```

Một đoạn code rất quen thuộc trong việc CRUD User được thực hiện trong controler UserController đúng không nào. Việc của chúng ta là phải triển khai việc tách phần truy xuất cũng như giao tiếp với database ở đây cụ thể là Model User.

Trước tiên ta tạo một thư mục nằm trong thư mục app của ứng dụng Laravel của bạn với tên là Repositories. Nhìn qua qua thì ta có thể hình dung thư mục này cùng cấp thư mục Http của app. Như tiêu đề của chỉ mục ta sẽ thiết kế tiếp 1 thư mục con ứng với mỗi Model- ở đây cụ thể là User. Trong thư mục User này ta thiết kế 2 file có tên là UserRepository.php và UserRepositoryInterface.php

Đây đại loại là cấu trúc như vậy.

<img style="width: 400px" src="https://i.imgur.com/sOd6ig1.png" />

Việc tiếp theo là xử lí file `UserRepository.php`

```
<?php

namespace App\Repositories\User;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @param $id
     * @return mixed
     */
    public function findById($id)
    {
        return User::findOrFail($id);
    }
}
```

Bên file `UserRepositoryInterface.php` sẽ xử lí như sau
```
<?php

namespace App\Repositories\User;

interface UserRepositoryInterface
{
    public function findById($id);
}
```

Sửa lại UserController một chút nào
```
<?php

namespace App\Http\Controllers;

use App\Repositories\User\UserRepositoryInterface;

class UserController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $user = $this->userRepository->findById($id);

        return view('admin.user.edit', compact('user'));
    }
}
```

Ở đây mình inject class `UserRepositoryInterface` vào `UserController` rồi gọi hàm `findById($id)` ra, vì vậy để code chạy được việc tiếp theo là phải `bind UserRepositoryInterface` vào Container sử dụng `UserRepository` trong đó.

Ta tìm đến file `AppServiceProvider.php` làm việc này trong function `register()`.

```
<?php

namespace App\Providers;

use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
    }
```

Các bạn xem code tại branch : [feature/create-user-repository](https://github.com/duongdung13/sample-repository-design-pattern/tree/feature/create-user-repository)
