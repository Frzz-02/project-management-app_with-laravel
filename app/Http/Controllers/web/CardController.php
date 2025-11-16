<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Project;
use App\Models\CardAssignment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * CardController
 * 
 * Controller untuk mengelola CRUD operations pada cards (task items dalam board kanban).
 * 
 * Fitur utama:
 * - List cards dengan filter, search, dan sorting
 * - Create card baru dalam board
 * - View detail card beserta assignments, subtasks, comments, time logs
 * - Update card information
 * - Delete card beserta semua relasi terkait
 * 
 * Related Tables:
 * - cards: Tabel utama untuk menyimpan card
 * - boards: Parent dari cards
 * - card_assignments: User yang ditugaskan ke card
 * - subtasks: Sub-tugas dalam card
 * - comments: Komentar pada card
 * - time_logs: Log waktu kerja pada card
 * 
 * @package App\Http\Controllers\web
 */
class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * Menampilkan daftar cards dengan fitur:
     * - Filter berdasarkan project, status, priority, board
     * - Search berdasarkan title dan description
     * - Sorting berdasarkan berbagai field
     * - Pagination (12 items per page)
     * - Statistics cards untuk dashboard
     * 
     * @param Request $request HTTP request dengan query parameters
     * @return \Illuminate\View\View View cards.index dengan data cards dan statistics
     */
    public function index(Request $request)
    {
        // Ambil user ID dan role yang sedang login
        $userId = Auth::id();
        $userRole = Auth::user()->role;
        
        // Project filter - default ke project pertama yang accessible jika tidak dispesifikasikan
        $projectId = $request->get('project_id');
        
        // Ambil semua projects yang bisa diakses user (sebagai creator atau member)
        $availableProjects = Project::where(function($query) use ($userId) {
            $query->where('created_by', $userId)
                  ->orWhereHas('members', function($q) use ($userId) {
                      $q->where('user_id', $userId);
                  });
        })->orderBy('project_name')->get();

        // Jika tidak ada project yang dipilih, gunakan project pertama yang tersedia
        if (!$projectId && $availableProjects->count() > 0) {
            $projectId = $availableProjects->first()->id;
        }

        // Query builder untuk cards dari project yang dipilih
        // Eager load relasi untuk menghindari N+1 query problem
        $query = Card::with(['board.project', 'creator'])
            ->whereHas('board', function($q) use ($projectId) {
                $q->where('project_id', $projectId);
            });
        
        // Jika user adalah member, hanya tampilkan cards yang ditugaskan kepada mereka
        if ($userRole === 'member') {
            $query->whereHas('assignments', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        // Filter: Search berdasarkan title atau description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('card_title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter: Status card (todo, in progress, review, done)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: Priority card (low, medium, high)
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter: Board spesifik dalam project
        if ($request->filled('board_id')) {
            $query->where('board_id', $request->board_id);
        }

        // Sorting berdasarkan field yang dipilih
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        
        switch ($sort) {
            case 'due_date':
                // Sort due_date: null values di akhir, lalu sort berdasarkan tanggal
                $query->orderByRaw('due_date IS NULL ASC, due_date ' . $direction);
                break;
            case 'priority':
                // Sort priority: high -> medium -> low
                $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low')");
                break;
            case 'status':
                // Sort status: todo -> in progress -> review -> done
                $query->orderByRaw("FIELD(status, 'todo', 'in progress', 'review', 'done')");
                break;
            default:
                // Sort default berdasarkan field yang dipilih
                $query->orderBy($sort, $direction);
                break;
        }

        // Paginate hasil dengan 12 items per page, preserve query string untuk filter
        $cards = $query->paginate(12)->withQueryString();
        
        // Ambil semua cards dari project untuk menghitung statistics
        $projectCardsQuery = Card::whereHas('board', function($q) use ($projectId) {
            $q->where('project_id', $projectId);
        });
        
        // Jika user adalah member, filter statistics juga hanya untuk cards yang ditugaskan
        if ($userRole === 'member') {
            $projectCardsQuery->whereHas('assignments', function($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }
        
        $projectCards = $projectCardsQuery->get();

        // Hitung statistics untuk dashboard/overview
        $statistics = [
            'total_cards' => $projectCards->count(),
            'todo_cards' => $projectCards->where('status', 'todo')->count(),
            'in_progress_cards' => $projectCards->where('status', 'in progress')->count(),
            'review_cards' => $projectCards->where('status', 'review')->count(),
            'done_cards' => $projectCards->where('status', 'done')->count(),
            'overdue_cards' => $projectCards->filter(function($card) {
                // Card overdue: punya due_date, sudah lewat, dan belum done
                return $card->due_date && now()->isAfter($card->due_date) && $card->status !== 'done';
            })->count(),
            'high_priority_cards' => $projectCards->where('priority', 'high')->count(),
        ];

        // Ambil info project yang sedang dipilih
        $selectedProject = $availableProjects->find($projectId);

        // Ambil boards yang tersedia dari project yang dipilih saja
        $availableBoards = \App\Models\Board::where('project_id', $projectId)
            ->orderBy('board_name')
            ->get();

        // Return view dengan semua data yang dibutuhkan
        return view('cards.index', [
            'cards' => $cards,
            'statistics' => $statistics,
            'availableProjects' => $availableProjects,
            'selectedProject' => $selectedProject,
            'selectedProjectId' => $projectId,
            'availableBoards' => $availableBoards,
            'filterData' => [
                'users' => collect(),
                'boards' => collect(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * 
     * Menampilkan form create card baru:
     * - Load boards yang accessible oleh user (creator atau member)
     * - User hanya bisa create card di project yang punya akses
     * 
     * @return \Illuminate\View\View View cards.create dengan daftar boards
     */
    public function create()
    {
        // Load boards dari projects yang bisa diakses user
        $userId = Auth::id();
        
        // Ambil boards dari project dimana user adalah creator atau member
        $boards = \App\Models\Board::with('project')
            ->whereHas('project', function($query) use ($userId) {
                $query->where('created_by', $userId)
                      ->orWhereHas('members', function($q) use ($userId) {
                          $q->where('user_id', $userId);
                      });
            })
            ->orderBy('board_name')
            ->get();

        return view('cards.create', [
            'boards' => $boards
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * Menyimpan card baru ke database dengan langkah:
     * 1. Validasi input data
     * 2. Hitung position terbaru untuk board yang dipilih
     * 3. Create card dengan status default 'todo'
     * 4. Set creator sebagai user yang sedang login
     * 5. Assign users jika ada
     * 
     * @param Request $request HTTP request dengan data card
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse JSON untuk AJAX atau Redirect untuk form biasa
     */
    public function store(Request $request)
    {
        $this->authorize('create', Card::class);
        
        // Validasi input
        $validatedData = $request->validate([
            'board_id' => 'required|exists:boards,id',
            'card_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:todo,in progress,review,done',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0|max:999.99',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            // Ambil position terbaru untuk board yang dipilih
            $lastPosition = Card::where('board_id', $validatedData['board_id'])->max('position') ?? 0;

            // Buat card baru dengan status dari form atau default 'todo'
            $card = Card::create([
                'board_id' => $validatedData['board_id'],
                'card_title' => $validatedData['card_title'],
                'description' => $validatedData['description'] ?? null,
                'status' => $validatedData['status'] ?? 'todo',
                'priority' => $validatedData['priority'],
                'due_date' => $validatedData['due_date'] ?? null,
                'estimated_hours' => $validatedData['estimated_hours'] ?? null,
                'position' => $lastPosition + 1,
                'created_by' => Auth::id()
            ]);

            // Assign users jika ada
            if ($request->has('assigned_users') && is_array($request->assigned_users)) {
                foreach ($request->assigned_users as $userId) {
                    CardAssignment::create([
                        'card_id' => $card->id,
                        'user_id' => $userId,
                        'assigned_by' => Auth::id()
                    ]);
                }
            }

            DB::commit();

            // Return JSON untuk AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Card created successfully!',
                    'card' => $card->load('assignments.user', 'board')
                ], 201);
            }

            // Return redirect untuk form submission biasa
            return redirect()->route('cards.show', $card)
                ->with('success', 'Card created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            // Return JSON error untuk AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Card creation failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'board_id' => $request->board_id,
                'trace' => $e->getTraceAsString()
            ]);

            // Return JSON error untuk AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create card: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create card: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * 
     * Menampilkan detail card beserta semua informasi terkait:
     * - Card information (title, description, status, priority, dll)
     * - Assignments (user yang ditugaskan)
     * - Subtasks (sub-tugas dalam card)
     * - Comments (komentar dari user)
     * - Time logs (log waktu kerja)
     * 
     * @param Card $card Instance card yang akan ditampilkan (route model binding)
     * @return \Illuminate\View\View View cards.show dengan data card
     */
    public function show(Card $card)
    {
        // Eager load relationships untuk performa
        $card->load([
            'board.project',
            'subtasks',
            'timeLogs.user',
            'assignments.user'
        ]);

        // Get ongoing time logs untuk concurrent tracking indicator
        $currentUserId = Auth::id();
        
        // Card tracking yang sedang berjalan (untuk user ini)
        $ongoingCardTracking = $card->timeLogs()
            ->where('user_id', $currentUserId)
            ->whereNull('subtask_id')  // Hanya card tracking
            ->whereNull('end_time')
            ->first();

        // Subtask tracking yang sedang berjalan (untuk user ini)
        $ongoingSubtaskTrackings = \App\Models\TimeLog::where('card_id', $card->id)
            ->where('user_id', $currentUserId)
            ->whereNotNull('subtask_id')  // Hanya subtask tracking
            ->whereNull('end_time')
            ->with('subtask')
            ->get();

        // Total active timers untuk card ini (untuk user ini)
        $activeTimersCount = ($ongoingCardTracking ? 1 : 0) + $ongoingSubtaskTrackings->count();

        return view('cards.show', compact(
            'card',
            'ongoingCardTracking',
            'ongoingSubtaskTrackings',
            'activeTimersCount'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     * 
     * Menampilkan form edit card:
     * - Load card yang akan diedit
     * - Load boards yang accessible oleh user
     * - Pre-fill form dengan data card yang ada
     * 
     * @param Card $card Instance card yang akan diedit (route model binding)
     * @return \Illuminate\View\View View cards.edit dengan data card dan boards
     */
    public function edit(Card $card)
    {
        // Load boards dari projects yang bisa diakses user
        $userId = Auth::id();
        
        // Ambil boards dari project dimana user adalah creator atau member
        $boards = \App\Models\Board::with('project')
            ->whereHas('project', function($query) use ($userId) {
                $query->where('created_by', $userId)
                      ->orWhereHas('members', function($q) use ($userId) {
                          $q->where('user_id', $userId);
                      });
            })
            ->orderBy('board_name')
            ->get();

        return view('cards.edit', [
            'card' => $card,
            'boards' => $boards
        ]);
    }

    /**
     * Update the specified resource in storage.
     * 
     * Mengupdate data card yang sudah ada:
     * 1. Authorization via Policy: Admin atau Team Lead dari project terkait
     * 2. Validasi input data (tanpa status dan actual_hours)
     * 3. Update field-field yang diizinkan
     * 4. Status dan actual_hours tidak diupdate di sini
     * 5. Creator (created_by) tidak diubah
     * 
     * Authorization (via CardPolicy):
     * - Admin (role 'admin' di tabel users) bisa edit semua card
     * - Team Lead (role 'team lead' di project_members) bisa edit card di project mereka
     * 
     * @param Request $request HTTP request dengan data card yang baru
     * @param Card $card Instance card yang akan diupdate (route model binding)
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse JSON untuk AJAX atau Redirect untuk form biasa
     */
    public function update(Request $request, Card $card)
    {
        // Authorization check via Policy
        $this->authorize('update', $card);
        
        // Validasi input termasuk board_id untuk memungkinkan pemindahan card
        $validatedData = $request->validate([
            'board_id' => 'required|exists:boards,id',
            'card_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0|max:999.99'
        ]);

        try {
            DB::beginTransaction();
            
            // Check if user has access to the new board (if board is being changed)
            if ($validatedData['board_id'] != $card->board_id) {
                $newBoard = \App\Models\Board::with('project')->findOrFail($validatedData['board_id']);
                
                // Verify user has access to the new board's project
                $userId = Auth::id();
                $hasAccess = $newBoard->project->created_by === $userId || 
                            $newBoard->project->members->contains('user_id', $userId);
                
                if (!$hasAccess) {
                    throw new \Illuminate\Auth\Access\AuthorizationException(
                        'Anda tidak memiliki akses ke board tujuan.'
                    );
                }
            }
            
            // Update card data termasuk board_id
            $card->update([
                'board_id' => $validatedData['board_id'],
                'card_title' => $validatedData['card_title'],
                'description' => $validatedData['description'] ?? null,
                'priority' => $validatedData['priority'],
                'due_date' => $validatedData['due_date'] ?? null,
                'estimated_hours' => $validatedData['estimated_hours'] ?? null
            ]);
            
            DB::commit();
            
            // Return JSON untuk AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Card berhasil diupdate!',
                    'card' => $card->load('assignments.user', 'board')
                ], 200);
            }

            return redirect()->route('boards.show', $card->board_id)
                ->with('success', 'Card berhasil diupdate!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            DB::rollBack();
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Admin or Team Lead can edit cards.'
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'Unauthorized. Only Admin or Team Lead can edit cards.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Card update failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'card_id' => $card->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update card: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update card: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * 
     * Menghapus card dari database:
     * 1. Authorization via Policy: Admin atau Team Lead dari project terkait
     * 2. Menghapus card beserta semua relasinya (cascade delete)
     * 3. Assignments, subtasks, comments, time logs akan terhapus otomatis
     * 
     * Authorization (via CardPolicy):
     * - Admin (role 'admin' di tabel users) bisa delete semua card
     * - Team Lead (role 'team lead' di project_members) bisa delete card di project mereka
     * 
     * @param Card $card Instance card yang akan dihapus
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse JSON untuk AJAX atau Redirect untuk form biasa
     */
    public function destroy(Request $request, Card $card)
    {
        // Authorization check via Policy
        $this->authorize('delete', $card);
        
        try {
            DB::beginTransaction();
            
            // Simpan board_id untuk redirect setelah delete
            $boardId = $card->board_id;
            $cardTitle = $card->card_title;
            
            // Hapus card dari database
            // Related data (assignments, subtasks, comments, time_logs) akan terhapus otomatis
            // karena foreign key constraint dengan onDelete('cascade')
            $card->delete();
            
            DB::commit();
            
            // Redirect kembali ke board dengan pesan sukses
            return redirect()->route('boards.show', $boardId)
                ->with('success', "Card '{$cardTitle}' berhasil dihapus!");
                
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Tidak memiliki izin. Hanya Admin atau Team Lead yang dapat menghapus card.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Card deletion failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'card_id' => $card->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Gagal menghapus card. Silakan coba lagi.');
        }
    }
}