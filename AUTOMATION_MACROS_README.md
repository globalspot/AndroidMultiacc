image.png# Automation Macros Editor

A powerful automation macros editor based on ReactFlow plugin for the Laravel dashboard, styled to match the reference screenshot interface.

## Features

### 🎯 **Core Functionality**
- **Visual Workflow Editor**: Drag-and-drop interface using ReactFlow
- **Multiple Node Types**: Start nodes, action nodes, and group nodes
- **Variable Support**: Define and manage variables for macro execution
- **Timer Integration**: Add delays and scheduling capabilities
- **Multiple Connections**: Support for complex workflow patterns

### 🎨 **Interface Design**
- **Left Sidebar**: Categorized actions and data types (matching reference screenshot)
- **Search Functionality**: Quick search through available actions
- **Main Canvas**: Visual workflow design area with grid background
- **Bottom Controls**: Canvas manipulation tools (grid, lock, link, etc.)

### 🔧 **Action Categories**
- **Device Operations**: Create, start, stop, configure devices
- **File Operations**: Read, write, delete, copy files
- **SMS Services**: Send, read, delete SMS messages
- **Database Operations**: Query, insert, update, delete records
- **Text Processing**: Extract, replace, split, join text
- **JSON/XML Processing**: Parse, generate, validate data
- **Image Processing**: Resize, crop, convert, filter images
- **Variable Processing**: Set, get, increment, decrement variables
- **Content Creation**: Generate text, images, reports
- **Timers & Delays**: Wait, schedule, repeat operations

## Installation & Setup

### 1. Dependencies
```bash
npm install reactflow @reactflow/node-resizer @reactflow/controls @reactflow/background @reactflow/minimap react react-dom
npm install @types/react @types/react-dom
```

### 2. Database Migration
```bash
php artisan migrate
```

### 3. Build Assets
```bash
npm run build
```

## Usage

### Creating a New Macro
1. Navigate to **Automation** → **Automation Macros** in the dashboard
2. Click **Create Macro**
3. Use the left sidebar to drag actions onto the canvas
4. Connect nodes to create workflow
5. Add variables and timers as needed
6. Save your macro

### Editing Existing Macros
1. From the macros list, click **Edit** on any macro
2. Modify the workflow by adding/removing nodes
3. Update connections between nodes
4. Modify variables and timers
5. Save changes

### Executing Macros
1. From the macro view, click **Execute Macro**
2. Monitor execution progress
3. View execution history and results

## File Structure

```
app/
├── Http/Controllers/
│   └── AutomationMacrosController.php    # Main controller
├── Models/
│   ├── AutomationMacro.php               # Macro model
│   ├── MacroVariable.php                 # Variable model
│   └── MacroTimer.php                    # Timer model
└── Policies/
    └── AutomationMacroPolicy.php         # Authorization policy

resources/
├── js/
│   └── macro-editor.jsx                  # ReactFlow editor component
└── views/automation/macros/
    ├── index.blade.php                   # Macros list
    ├── create.blade.php                  # Create macro
    ├── edit.blade.php                    # Edit macro
    └── show.blade.php                    # View macro

database/migrations/
├── create_automation_macros_table.php    # Macros table
├── create_macro_variables_table.php      # Variables table
└── create_macro_timers_table.php         # Timers table
```

## API Endpoints

### Macros Management
- `GET /automation/macros` - List all macros
- `GET /automation/macros/create` - Create macro form
- `POST /automation/macros` - Store new macro
- `GET /automation/macros/{macro}` - View macro
- `GET /automation/macros/{macro}/edit` - Edit macro form
- `PUT /automation/macros/{macro}` - Update macro
- `DELETE /automation/macros/{macro}` - Delete macro
- `POST /automation/macros/{macro}/execute` - Execute macro

### Action Types
- `GET /automation/action-types` - Get available action types

## Customization

### Adding New Action Types
1. Modify `getActionTypes()` method in `AutomationMacrosController`
2. Add new categories and actions
3. Update the React component to handle new actions

### Custom Node Types
1. Create new node components in `macro-editor.jsx`
2. Add to `nodeTypes` object
3. Implement custom logic and styling

### Styling
- Uses Tailwind CSS for styling
- Custom node styling in React components
- Responsive design for different screen sizes

## Security Features

- **User Isolation**: Users can only access their own macros
- **Authorization Policies**: Proper permission checks for all operations
- **CSRF Protection**: Laravel's built-in CSRF protection
- **Input Validation**: Comprehensive validation for all inputs

## Performance Considerations

- **Lazy Loading**: Action types loaded on demand
- **Efficient Queries**: Optimized database queries with proper indexing
- **Asset Optimization**: Vite build process for optimized JavaScript/CSS

## Browser Support

- Modern browsers with ES6+ support
- ReactFlow compatibility
- Responsive design for mobile and desktop

## Troubleshooting

### Common Issues
1. **Assets not loading**: Run `npm run build`
2. **Database errors**: Check migrations with `php artisan migrate:status`
3. **Permission errors**: Verify user authentication and policies
4. **ReactFlow not rendering**: Check browser console for JavaScript errors

### Debug Mode
Enable Laravel debug mode in `.env` for detailed error information.

## Future Enhancements

- **Real-time Collaboration**: Multiple users editing same macro
- **Version Control**: Macro versioning and rollback
- **Advanced Scheduling**: Cron-like scheduling capabilities
- **Integration APIs**: Connect with external automation tools
- **Analytics Dashboard**: Execution statistics and performance metrics
- **Template Library**: Pre-built macro templates
- **Conditional Logic**: If/else statements in workflows
- **Error Handling**: Robust error handling and recovery

## Contributing

1. Follow Laravel coding standards
2. Add tests for new functionality
3. Update documentation for changes
4. Ensure responsive design compatibility
5. Test across different browsers

## License

This automation macros editor is part of the Android Multiaccounting platform.

