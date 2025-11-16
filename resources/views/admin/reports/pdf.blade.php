<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Report - {{ $generated_at }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            line-height: 1.5;
            color: #333;
            padding: 30px 35px;
            background-color: #ffffff;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding: 15px 20px 15px 20px;
            border-bottom: 3px solid #3b82f6;
            background-color: #f8fafc;
        }
        
        .header h1 {
            font-size: 26pt;
            color: #1e40af;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        
        .header p {
            font-size: 10pt;
            color: #6b7280;
            margin: 3px 0;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
            padding: 0 5px;
        }
        
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 12px;
            padding: 8px 12px;
            border-bottom: 2px solid #e5e7eb;
            background-color: #f8fafc;
            border-left: 4px solid #3b82f6;
        }
        
        .overview-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #d1d5db;
        }
        
        .overview-row {
            display: table-row;
        }
        
        .overview-cell {
            display: table-cell;
            width: 25%;
            padding: 18px 15px;
            border: 1px solid #e5e7eb;
            text-align: center;
            background-color: #ffffff;
        }
        
        .overview-cell:nth-child(odd) {
            background-color: #f9fafb;
        }
        
        .overview-label {
            font-size: 8pt;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .overview-value {
            font-size: 20pt;
            font-weight: bold;
            color: #1e40af;
            margin-top: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #d1d5db;
        }
        
        table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            text-align: left;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        table td {
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            font-size: 9pt;
            vertical-align: middle;
        }
        
        table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        table tr:hover {
            background-color: #f3f4f6;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        
        .badge-gray {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .footer {
            position: fixed;
            bottom: 15px;
            left: 35px;
            right: 35px;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            padding: 12px 20px;
            border-top: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        @page {
            margin: 20mm 15mm;
            size: A4 landscape;
        }
        
        /* Spacing helpers */
        .mb-10 {
            margin-bottom: 10px;
        }
        
        .mb-15 {
            margin-bottom: 15px;
        }
        
        .mt-10 {
            margin-top: 10px;
        }
        
        /* Empty state styling */
        .empty-state {
            padding: 30px 20px;
            text-align: center;
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            color: #166534;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📊 Admin Dashboard Report</h1>
            <p>Comprehensive Project Management Analytics</p>
            <p style="font-size: 8pt; margin-top: 5px;">Generated: {{ $generated_at }}</p>
        </div>
        
        <!-- Overview Statistics -->
        <div class="section">
            <div class="section-title">📈 Overview Statistics</div>
            <div class="overview-grid">
                <div class="overview-row">
                    <div class="overview-cell">
                        <div class="overview-label">Total Projects</div>
                        <div class="overview-value">{{ $overview['total_projects'] }}</div>
                    </div>
                    <div class="overview-cell">
                        <div class="overview-label">Active Users</div>
                        <div class="overview-value">{{ $overview['active_users'] }}</div>
                    </div>
                    <div class="overview-cell">
                        <div class="overview-label">Total Tasks</div>
                        <div class="overview-value">{{ $overview['total_cards'] }}</div>
                    </div>
                    <div class="overview-cell">
                        <div class="overview-label">Completion Rate</div>
                        <div class="overview-value">{{ $overview['completion_rate'] }}%</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Project Performance -->
        <div class="section mb-15">
            <div class="section-title">💚 Project Health Score (Top 10)</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%;">Project Name</th>
                        <th style="width: 15%;">Creator</th>
                        <th style="width: 12%;" class="text-center">Health Status</th>
                        <th style="width: 8%;" class="text-center">Tasks</th>
                        <th style="width: 10%;" class="text-center">Completed</th>
                        <th style="width: 10%;" class="text-center">Progress</th>
                        <th style="width: 12%;" class="text-center">Days Left</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                    <tr>
                        <td><strong>{{ $project->project_name }}</strong></td>
                        <td>{{ $project->creator->full_name ?? 'Unknown' }}</td>
                        <td class="text-center">
                            @if($project->health_status === 'On Track')
                                <span class="badge badge-success">On Track</span>
                            @elseif($project->health_status === 'At Risk')
                                <span class="badge badge-warning">At Risk</span>
                            @else
                                <span class="badge badge-danger">Overdue</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $project->total_tasks ?? 0 }}</td>
                        <td class="text-center">{{ $project->completed_tasks ?? 0 }}</td>
                        <td class="text-center"><strong>{{ $project->completion_percentage ?? 0 }}%</strong></td>
                        <td class="text-center">
                            @if($project->days_remaining < 0)
                                <span style="color: #dc2626; font-weight: bold;">{{ abs($project->days_remaining) }} days late</span>
                            @else
                                <span style="color: #059669;">{{ $project->days_remaining }} days</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="page-break"></div>
        
        <!-- Team Performance -->
        <div class="section mb-15">
            <div class="section-title">🏆 Team Performance Leaderboard (Top 10)</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;" class="text-center">Rank</th>
                        <th style="width: 25%;">Member Name</th>
                        <th style="width: 15%;">Username</th>
                        <th style="width: 12%;" class="text-center">Status</th>
                        <th style="width: 10%;" class="text-center">Assigned</th>
                        <th style="width: 10%;" class="text-center">Completed</th>
                        <th style="width: 15%;" class="text-center">Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teamMembers as $index => $member)
                    <tr>
                        <td class="text-center" style="font-size: 14pt;">
                            @if($index === 0)
                                🥇
                            @elseif($index === 1)
                                🥈
                            @elseif($index === 2)
                                🥉
                            @else
                                <strong>{{ $index + 1 }}</strong>
                            @endif
                        </td>
                        <td><strong>{{ $member->full_name }}</strong></td>
                        <td style="color: #6b7280;">@{{ $member->username }}</td>
                        <td class="text-center">
                            @if($member->current_task_status === 'working')
                                <span class="badge badge-success">Working</span>
                            @else
                                <span class="badge badge-gray">Idle</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $member->assigned_cards ?? 0 }}</td>
                        <td class="text-center" style="color: #059669; font-weight: bold;">{{ $member->completed_cards ?? 0 }}</td>
                        <td class="text-center"><strong style="color: #1e40af; font-size: 10pt;">{{ $member->completion_rate ?? 0 }}%</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Overdue Tasks -->
        <div class="section mb-15">
            <div class="section-title">🚨 Critical Alerts - Overdue Tasks (Top 20)</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%;">Task Title</th>
                        <th style="width: 18%;">Project</th>
                        <th style="width: 12%;">Creator</th>
                        <th style="width: 10%;" class="text-center">Due Date</th>
                        <th style="width: 12%;" class="text-center">Days Overdue</th>
                        <th style="width: 10%;" class="text-center">Priority</th>
                        <th style="width: 10%;" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @if($overdueTasks->count() > 0)
                        @foreach($overdueTasks as $task)
                        <tr>
                            <td><strong>{{ $task->card_title }}</strong></td>
                            <td>{{ $task->board->project->project_name ?? 'Unknown' }}</td>
                            <td>{{ $task->creator->full_name ?? 'Unknown' }}</td>
                            <td class="text-center" style="color: #6b7280;">{{ $task->due_date }}</td>
                            <td class="text-center">
                                <span class="badge badge-danger" style="font-size: 8pt;">{{ $task->days_overdue }} days</span>
                            </td>
                            <td class="text-center">
                                @if($task->priority === 'high')
                                    <span class="badge badge-danger">High</span>
                                @elseif($task->priority === 'medium')
                                    <span class="badge badge-warning">Medium</span>
                                @else
                                    <span class="badge badge-info">Low</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-gray">{{ ucfirst($task->status) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="empty-state">
                                <strong style="font-size: 11pt;">🎉 No overdue tasks! Everything is on track!</strong>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Summary Stats -->
        <div class="section">
            <div class="section-title">📊 Summary Statistics</div>
            <table>
                <tr>
                    <td style="width: 30%; font-weight: bold; background-color: #f9fafb;">Total Projects</td>
                    <td style="width: 20%;" class="text-right"><strong>{{ $overview['total_projects'] }}</strong></td>
                    <td style="width: 30%; font-weight: bold; background-color: #f9fafb;">Active Projects</td>
                    <td style="width: 20%;" class="text-right"><strong>{{ $overview['active_projects'] }}</strong></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background-color: #f9fafb;">Total Team Members</td>
                    <td class="text-right"><strong>{{ $overview['total_users'] }}</strong></td>
                    <td style="font-weight: bold; background-color: #f9fafb;">Active Members</td>
                    <td class="text-right"><strong>{{ $overview['active_users'] }}</strong></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background-color: #f9fafb;">Total Tasks</td>
                    <td class="text-right"><strong>{{ $overview['total_cards'] }}</strong></td>
                    <td style="font-weight: bold; background-color: #f9fafb;">Completed Tasks</td>
                    <td class="text-right"><strong style="color: #059669;">{{ $overview['completed_cards'] }}</strong></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; background-color: #f9fafb;">Overdue Tasks</td>
                    <td class="text-right"><strong style="color: #dc2626;">{{ $overview['overdue_cards'] }}</strong></td>
                    <td style="font-weight: bold; background-color: #f9fafb;">Overall Completion Rate</td>
                    <td class="text-right"><strong style="color: #059669; font-size: 11pt;">{{ $overview['completion_rate'] }}%</strong></td>
                </tr>
            </table>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p style="font-weight: 600; color: #374151;">Project Management System - Admin Dashboard Report</p>
            <p style="margin-top: 3px;">© {{ date('Y') }} - Generated on {{ $generated_at }}</p>
        </div>
    </div>
</body>
</html>
