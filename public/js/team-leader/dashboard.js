
// Team Leader Dashboard Charts
document.addEventListener('DOMContentLoaded', function() {
    
    // Task Status Donut Chart
    fetch('/team-leader/dashboard/chart/task-status')
        .then(res => res.json())
        .then(data => {
            const canvas = document.getElementById('taskStatusChart');
            
            if (!canvas) {
                console.error('Task status chart canvas not found');
                return;
            }
            
            // Check if data exists
            if (!data.data || data.data.length === 0) {
                canvas.parentElement.innerHTML = '<p class="text-center text-gray-500 py-8">No task data available</p>';
                return;
            }
            
            const ctx = canvas.getContext('2d');
            const colors = {
                'todo': '#94a3b8',      // Gray
                'in progress': '#3b82f6', // Blue
                'review': '#f59e0b',    // Orange
                'done': '#10b981'       // Green
            };
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.data.map(d => d.label),
                    datasets: [{
                        data: data.data.map(d => d.value),
                        backgroundColor: data.data.map(d => colors[d.status]),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading task status chart:', error);
            const canvas = document.getElementById('taskStatusChart');
            if (canvas) {
                canvas.parentElement.innerHTML = '<p class="text-center text-red-500 py-8">Error loading chart data</p>';
            }
        });
    
    // Team Workload Bar Chart
    fetch('/team-leader/dashboard/chart/team-workload')
        .then(res => res.json())
        .then(data => {
            const canvas = document.getElementById('teamWorkloadChart');
            
            if (!canvas) {
                console.error('Team workload chart canvas not found');
                return;
            }
            
            // Check if data exists
            if (!data.data || data.data.length === 0) {
                canvas.parentElement.innerHTML = '<p class="text-center text-gray-500 py-8">No team workload data available</p>';
                return;
            }
            
            const ctx = canvas.getContext('2d');
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.data.map(d => d.name),
                    datasets: [
                        {
                            label: 'Completed',
                            data: data.data.map(d => d.completed),
                            backgroundColor: '#10b981'
                        },
                        {
                            label: 'In Progress',
                            data: data.data.map(d => d.in_progress),
                            backgroundColor: '#3b82f6'
                        },
                        {
                            label: 'Pending',
                            data: data.data.map(d => d.pending),
                            backgroundColor: '#94a3b8'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading team workload chart:', error);
            const canvas = document.getElementById('teamWorkloadChart');
            if (canvas) {
                canvas.parentElement.innerHTML = '<p class="text-center text-red-500 py-8">Error loading chart data</p>';
            }
        });
});
