# AGENTS.md - Developer Guidelines for LMS2025

## Project Overview
- **Stack**: Laravel 12 + Vue 3 + Vuetify + Pinia + Vuex
- **Database**: PostgreSQL (configured in .env)
- **Frontend Build**: Vite 6

---

## Build, Test & Development Commands

### PHP/Laravel Commands
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=UserModelTest

# Run specific test method
php artisan test --filter=test_user_can_be_created

# Run tests with coverage (if installed)
php artisan test --coverage

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run migrations
php artisan migrate
php artisan migrate:fresh  # Drops all tables and re-runs migrations

# Seed database
php artisan db:seed

# Start development server
php artisan serve --host=0.0.0.0 --port=8000

# Run Laravel Pint (code style)
./vendor/bin/pint
./vendor/bin/pint --test  # Dry run (check only)
```

### Node/Vue Commands
```bash
# Install dependencies
npm install

# Development server (with hot reload)
npm run dev

# Production build
npm run build

# Watch mode for development
npm run dev -- --watch
```

### Combined Development
```bash
# Full dev stack (requires composer, npm installed)
composer dev
```

---

## Code Style Guidelines

### PHP (Laravel)

#### Naming Conventions
- **Classes**: `PascalCase` (e.g., `UserController`, `CourseService`)
- **Methods**: `camelCase` (e.g., `getUserById()`, `updateCourse()`)
- **Variables**: `camelCase` (e.g., `$userData`, `$courseList`)
- **Constants**: `UPPER_SNAKE_CASE` (e.g., `MAX_UPLOAD_SIZE`)
- **Database Tables**: `snake_case`, plural (e.g., `users`, `group2learnings`)
- **Database Columns**: `snake_case` (e.g., `created_at`, `is_active`)

#### Import Guidelines
- Use fully qualified class names or explicit imports
- Group imports: built-in PHP → Composer → Laravel → Custom
- Sort alphabetically within groups

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Controllers\Controller;
```

#### Error Handling
- Use `try-catch` for operations that may fail
- Return appropriate HTTP status codes
- Use Laravel's validation rules in controllers
- Log errors with `Log::error()` or `logger()`

```php
try {
    $user = User::create($validated);
    return response()->json(['user' => $user], 201);
} catch (\Exception $e) {
    Log::error('User creation failed: ' . $e->getMessage());
    return response()->json(['error' => 'Failed to create user'], 500);
}
```

#### Type Hints & Return Types
- Use strict type hints where possible
- Add return types to methods

```php
public function index(): \Illuminate\Http\JsonResponse
{
    return response()->json(['users' => $users]);
}

public function store(Request $request): User
{
    // ...
}
```

---

### Vue/JavaScript

#### Naming Conventions
- **Components**: `PascalCase` (e.g., `UserList.vue`, `CourseCard.vue`)
- **Store (Pinia)**: `camelCase` (e.g., `userStore.js`, `courseStore.js`)
- **Vuex Store**: `camelCase` (e.g., `index.js`)
- **Methods/Props**: `camelCase`
- **Constants**: `UPPER_SNAKE_CASE`

#### Import Order (Vue SFC)
```javascript
// 1. Vue/Vue Router/Pinia
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/userStore'

// 2. Vuex (if using)
import { mapActions } from 'vuex'

// 3. API/Services
import userApi from '@/api/user.api'

// 4. Components
import UserCard from '@/components/UserCard.vue'

// 5. Utils
import { formatDate } from '@/utils/date'
```

#### Vue 3 Composition API Preferred
- Use `<script setup>` for new components
- Use Pinia for state management (preferred over Vuex for new code)
- Keep Vuex for legacy components that still use it

```javascript
// Preferred
const user = ref(null)
const loading = ref(false)

const fetchUser = async (id) => {
  loading.value = true
  try {
    user.value = await userApi.get(id)
  } finally {
    loading.value = false
  }
}
```

#### Vuex (Legacy)
- Use `mapState`, `mapGetters`, `mapActions` for compatibility
- Modules in `resources/js/store/index.js`

---

## Database & Models

### Eloquent Conventions
- Use `$fillable` for mass assignment
- Use `$hidden` for sensitive data
- Use `$casts` for type conversion
- Define relationships using methods

```php
class User extends Authenticatable
{
    protected $fillable = ['email', 'password', 'fio', 'group_id'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['is_active' => 'boolean'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
```

### Migrations
- Use `php artisan make:migration` to create migrations
- Always add `nullable()` for new optional fields
- Avoid changing existing migrations; create new ones

---

## API Development

### RESTful Conventions
- `GET /resources` - List
- `GET /resources/{id}` - Show
- `POST /resources` - Store
- `PUT /resources/{id}` - Update
- `DELETE /resources/{id}` - Destroy

### Request Validation
- Use Form Requests for complex validation
- Return meaningful error messages

```php
public function store(UserRequest $request)
{
    // Validation is handled by UserRequest
    $user = User::create($request->validated());
    return response()->json($user, 201);
}
```

---

## Frontend Components

### Vuetify Usage
- Use Vuetify components for UI (v-btn, v-card, v-dialog, etc.)
- Follow Vuetify 3 conventions

### Component Structure
```vue
<template>
  <!-- Markup here -->
</template>

<script setup>
import { ref, computed } from 'vue'
// imports
</script>

<style scoped>
/* Component styles */
</style>
```

---

## Testing Guidelines

### Test Structure
- Unit tests in `tests/Unit/`
- Feature tests in `tests/Feature/`
- Use `RefreshDatabase` trait for database tests

```php
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created(): void
    {
        // test implementation
    }
}
```

### Best Practices
- One assertion per test when possible
- Use descriptive test names: `test_<action>_<expected_result>`
- Mock external services
- Use factories for test data

---

## Common Issues & Solutions

### Vite Build Errors
- Run `npm run build` before deploying
- Check for missing npm packages (vuex was added manually)

### Database Issues
- Ensure PostgreSQL is running
- Run `php artisan migrate:fresh --seed` for fresh setup

### Vue Component Errors
- Check for missing imports
- Ensure vuex is properly configured for legacy components

---

## File Locations

- **Controllers**: `app/Http/Controllers/`
- **Models**: `app/Models/`
- **Migrations**: `database/migrations/`
- **Routes**: `routes/api.php`, `routes/web.php`
- **Vue Components**: `resources/js/Pages/`,/`
- ** `resources/js/componentsVue Stores**: `resources/js/stores/` (Pinia), `resources/js/store/` (Vuex)
- **API**: `resources/js/api/`
- **Tests**: `tests/Unit/`, `tests/Feature/`

---

## Local AI Models (Ollama)

This project is configured to use local AI models via Ollama.

### Available Models
The following models are available (installed locally):
- **Qwen3 14B** - `qwen3:14b-q4_K_M`
- **DeepSeek R1 14B** - `deepseek-r1:14b-qwen-distill-q4_K_M`
- **DeepCoder** - `deepcoder:latest`
- **Qwen3 Coder 30B** - `qwen3-coder:30b`
- **GPT-Oss 20B** - `gpt-oss:20b`
- **WhiteRabbitNeo 8B** - `WhiteRabbitNeo/Llama-3.1-WhiteRabbitNeo-2-8B`

### Using with OpenCode
Run in the project directory:
```bash
opencode
```

Select Ollama provider when prompted (models are pre-configured in `opencode.json`).

### Adding New Models
1. Pull the model: `ollama pull <model-name>`
2. Add to `opencode.json`:
```json
{
  "provider": {
    "ollama": {
      "models": {
        "model-name": {
          "name": "Display Name"
        }
      }
    }
  }
}
```
