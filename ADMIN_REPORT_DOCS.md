# Admin Report System - Documentation

## 📊 Overview
Comprehensive admin reporting system untuk Project Management App dengan analytics, visualizations, dan export functionality.

## ✨ Features

### 1. **Overview Statistics** (4 Cards)
- Total Projects dengan active count
- Active Users dari total users
- Total Tasks dengan overdue count
- Completion Rate percentage

### 2. **Data Visualizations** (6 Charts dengan Chart.js 4.4.0)
- **Pie Chart**: Project Status Distribution (On Track, Due Soon, Overdue)
- **Donut Chart**: Task Status Distribution (Todo, In Progress, Review, Done)
- **Horizontal Bar**: Project Completion Timeline dengan health status colors
- **Stacked Bar**: Team Workload Distribution (Completed, In Progress, Assigned)
- **Line Chart**: Estimated vs Actual Hours (6 months trend)

### 3. **Data Tables**
- **Project Health Score**: Health status, completion percentage, team size, overdue tasks, days remaining
- **Team Performance Leaderboard**: Rankings dengan badges 🥇🥈🥉, completion rates, hours tracking
- **Critical Alerts**: Overdue tasks dengan priority highlighting

### 4. **Filters**
- Date Range (From - To)
- Clear Filters button
- Real-time data refresh

### 5. **Export Options**
- **Excel Export**: Multi-sheet workbook dengan:
  - Overview Statistics sheet
  - Project Performance sheet
  - Team Performance sheet
  - Overdue Tasks sheet
- **PDF Export**: Landscape A4 dengan professional formatting
- **Print**: Browser print dengan optimized CSS

## 🚀 Installation

### Packages Required
```bash
composer require maatwebsite/excel barryvdh/laravel-dompdf
```

### Files Created
```
app/
├── Http/
│   ├── Controllers/
│   │   └── AdminReportController.php      # Main controller dengan 8 methods
│   └── Middleware/
│       └── AdminMiddleware.php             # Admin access validation
├── Exports/
│   └── AdminReportExport.php               # Excel export dengan 4 sheets
resources/
└── views/
    └── admin/
        └── reports/
            ├── index.blade.php              # Main dashboard view
            └── pdf.blade.php                # PDF template
```

## 📍 Routes

### Web Routes (Auth + Admin Middleware)
```php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/reports', [AdminReportController::class, 'index'])
        ->name('admin.reports.index');
});
```

### API Endpoints (Called by Alpine.js)
```php
GET /admin/reports/overview-stats           # Overview statistics
GET /admin/reports/project-performance      # Project health data
GET /admin/reports/team-performance         # Team rankings
GET /admin/reports/task-analytics           # Task distributions & trends
GET /admin/reports/overdue-tasks            # Critical alerts
GET /admin/reports/export-excel             # Download Excel file
GET /admin/reports/export-pdf               # Download PDF file
```

## 💻 Usage

### Access Dashboard
1. Login sebagai user dengan role `admin`
2. Navigate ke `/admin/reports`
3. Dashboard akan auto-load semua data

### Use Filters
```javascript
// Date Range Filter
- Pilih "Date From" dan "Date To"
- Data akan auto-refresh setelah filter applied
- Click "Clear Filters" untuk reset

// Query Parameters
GET /admin/reports/overview-stats?date_from=2025-01-01&date_to=2025-12-31
```

### Export Data
```javascript
// Excel Export
- Click "Export Excel" button
- Multi-sheet workbook akan di-download
- Format: admin_report_YYYY-MM-DD_HHMMSS.xlsx

// PDF Export
- Click "Export PDF" button
- Landscape A4 PDF akan di-download
- Format: admin_report_YYYY-MM-DD_HHMMSS.pdf

// Print
- Click "Print" button
- Browser print dialog akan muncul
- Optimized untuk paper printing
```

## 🔧 Controller Methods

### AdminReportController

#### `index()`
```php
// Render main dashboard view
return view('admin.reports.index');
```

#### `getOverviewStats(Request $request)`
```php
// Returns: Overview statistics dengan caching (5 minutes)
// Parameters: date_from, date_to (optional)
// Response: JSON dengan total projects, users, cards, completion rate, distributions
```

#### `getProjectPerformance(Request $request)`
```php
// Returns: Project health scores dengan complex calculations
// Parameters: project_id (optional)
// Response: JSON array projects dengan health_status, completion_percentage, days_remaining
// Health Status: "Overdue" | "At Risk" | "Needs Attention" | "On Track"
```

#### `getTeamPerformance(Request $request)`
```php
// Returns: Team member rankings dan workload analysis
// Parameters: user_id (optional)
// Response: JSON array users dengan badges (🥇🥈🥉), completion_rate, hours tracking
```

#### `getTaskAnalytics(Request $request)`
```php
// Returns: Task distributions dan trends
// Response: JSON dengan priority_distribution, status_distribution, monthly_trend, hours_comparison
```

#### `getOverdueTasks(Request $request)`
```php
// Returns: Critical overdue tasks
// Response: JSON array tasks dengan days_overdue, sorted by due_date + priority
```

#### `exportExcel(Request $request)`
```php
// Returns: Excel file download
// Uses: App\Exports\AdminReportExport
// Format: Multi-sheet XLSX dengan styling
```

#### `exportPdf(Request $request)`
```php
// Returns: PDF file download
// Uses: Barryvdh\DomPDF\Facade\Pdf
// Template: admin.reports.pdf blade view
// Paper: A4 Landscape
```

## 📊 Data Structure

### Overview Stats Response
```json
{
  "success": true,
  "data": {
    "total_projects": 15,
    "active_projects": 12,
    "active_users": 8,
    "total_users": 10,
    "total_cards": 150,
    "completed_cards": 120,
    "completion_rate": 80.00,
    "overdue_cards": 5,
    "project_status_distribution": {
      "on_track": 10,
      "due_soon": 3,
      "overdue": 2
    },
    "task_status_distribution": {
      "todo": 20,
      "in progress": 30,
      "review": 10,
      "done": 120
    }
  },
  "generated_at": "2025-11-13 10:30:45"
}
```

### Project Performance Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "project_name": "Project Alpha",
      "creator": "John Doe",
      "deadline": "2025-12-31",
      "days_remaining": 48,
      "team_size": 5,
      "total_tasks": 25,
      "completed_tasks": 20,
      "overdue_tasks": 2,
      "completion_percentage": 80.00,
      "total_hours_spent": 120.50,
      "total_hours_estimated": 150.00,
      "health_status": "On Track"
    }
  ]
}
```

### Team Performance Response
```json
{
  "success": true,
  "data": [
    {
      "rank": 1,
      "id": 5,
      "full_name": "Jane Smith",
      "username": "jane",
      "current_status": "working",
      "assigned_cards": 30,
      "in_progress_cards": 5,
      "completed_cards": 25,
      "overdue_tasks": 1,
      "total_estimated_hours": 80.00,
      "total_actual_hours": 75.50,
      "completion_rate": 83.33,
      "badge": "🥇"
    }
  ]
}
```

## 🎨 Frontend Components

### Alpine.js Component
```javascript
function adminReportData() {
  return {
    loading: true,
    filters: { date_from: '', date_to: '' },
    overview: {},
    projectPerformance: [],
    teamPerformance: [],
    taskAnalytics: {},
    overdueTasks: [],
    charts: {},
    
    async loadAllData() { /* Load semua endpoints dengan Promise.all */ },
    async refreshAllData() { /* Refresh semua data */ },
    initializeCharts() { /* Initialize Chart.js */ }
  }
}
```

### Chart.js Configurations
```javascript
// 1. Pie Chart - Project Status
type: 'pie'
colors: ['#10b981', '#f59e0b', '#ef4444']

// 2. Donut Chart - Task Status
type: 'doughnut'
colors: ['#94a3b8', '#3b82f6', '#f59e0b', '#10b981']

// 3. Horizontal Bar - Project Timeline
type: 'bar', indexAxis: 'y'
colors: Dynamic based on health_status

// 4. Stacked Bar - Team Workload
type: 'bar', stacked: true
datasets: [Completed, In Progress, Assigned]

// 5. Line Chart - Hours Comparison
type: 'line', tension: 0.4
datasets: [Estimated Hours, Actual Hours]
```

## 🔐 Security

### Middleware Stack
```php
$this->middleware(['auth', 'admin']);
```

### Admin Validation
```php
// AdminMiddleware checks:
1. User is authenticated
2. User role === 'admin'
3. Abort 403 if not admin
```

### Authorization
- Semua routes protected dengan `auth` middleware
- Admin-only access dengan `admin` middleware
- Registered di `bootstrap/app.php` sebagai 'admin' alias

## 🚀 Performance

### Caching Strategy
```php
// Overview stats cached for 5 minutes
Cache::remember("admin_overview_stats_{$dateFrom}_{$dateTo}", 300, function() {
    // Complex queries...
});
```

### Query Optimization
- Eager loading dengan `with()` untuk relationships
- `selectRaw()` untuk calculated fields
- `groupBy()` untuk aggregations
- MySQL-specific functions: DATE_FORMAT, DATEDIFF, CURDATE()

### Database Queries
- Project Performance: 1 query dengan multiple joins
- Team Performance: 1 query dengan aggregations
- Task Analytics: 4 separate queries (priority, status, monthly, hours)
- Overdue Tasks: 1 query dengan sorting

## 📝 Notes

### Health Status Logic
```php
CASE 
    WHEN deadline < TODAY THEN "Overdue"
    WHEN overdue_tasks > 5 THEN "At Risk"
    WHEN completion >= 80% THEN "On Track"
    ELSE "Needs Attention"
END
```

### Team Rankings
- Sorted by `assigned_cards DESC`
- Top 3 get emoji badges (🥇🥈🥉)
- Completion rate = completed / assigned * 100

### Print Optimization
```css
@media print {
  .no-print { display: none; }
  @page { margin: 1.5cm; size: A4 landscape; }
  /* Remove shadows, optimize colors, adjust sizing */
}
```

## 🐛 Troubleshooting

### Issue: "Undefined type" errors in IDE
**Solution**: Run `composer dump-autoload` after package installation

### Issue: Excel export fails
**Solution**: Check `storage/` directory permissions (writable)

### Issue: PDF fonts missing
**Solution**: DomPDF will use default fonts, or publish config untuk custom fonts

### Issue: Charts not displaying
**Solution**: Check browser console for Chart.js errors, ensure data structure matches

### Issue: 403 Forbidden on access
**Solution**: Ensure user role is 'admin' in database

## 🔄 Testing

### Manual Testing Checklist
```
✅ Access dashboard (/admin/reports)
✅ Check all 4 overview cards display data
✅ Verify 6 charts render correctly
✅ Test date range filter
✅ Test clear filters
✅ Test refresh button
✅ Test Excel export download
✅ Test PDF export download
✅ Test print functionality
✅ Verify overdue tasks table
✅ Verify project health table
✅ Verify team performance table
✅ Test on different screen sizes (responsive)
```

### API Testing
```bash
# Test with curl or Postman
curl -X GET "http://localhost:8000/admin/reports/overview-stats" \
  -H "Cookie: laravel_session=YOUR_SESSION"
```

## 📦 Dependencies

### Backend
- Laravel 12+
- maatwebsite/excel ^3.1
- barryvdh/laravel-dompdf ^3.1

### Frontend
- Alpine.js 3.x (included in layouts/app.blade.php)
- Chart.js 4.4.0 (CDN)
- Tailwind CSS (configured)

## 📄 License
Part of Project Management System - Internal Use

---

**Created**: November 2025  
**Last Updated**: November 13, 2025  
**Version**: 1.0.0
