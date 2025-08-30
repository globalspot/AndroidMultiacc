import React, { useState, useCallback, useRef, useEffect } from 'react';
import ReactDOM from 'react-dom/client';
import { createPortal } from 'react-dom';
import ReactFlow, {
    MiniMap,
    Controls,
    Background,
    useNodesState,
    useEdgesState,
    addEdge,
    applyEdgeChanges,
    applyNodeChanges,
    MarkerType,
    Handle,
    Position,
} from 'reactflow';
import { NodeResizer } from '@reactflow/node-resizer';
import 'reactflow/dist/style.css';
import '@reactflow/node-resizer/dist/style.css';

// Custom Node Types
const ActionNode = ({ data, selected, id }) => {
    return (
    <div className={`px-4 py-3 rounded-lg border-2 min-w-[200px] ${selected ? 'border-blue-500 shadow-lg' : 'border-gray-300'} ${data.type === 'notification' ? 'bg-red-100 border-red-300' : 'bg-green-100 border-green-300'} relative overflow-visible`}>
        {/* ReactFlow connection handles */}
        <Handle type="source" position={Position.Top} id={`out-${id}`} style={{ background: '#3b82f6' }} />
        <Handle type="target" position={Position.Bottom} id={`in-${id}`} style={{ background: '#10b981' }} />

        <div className="flex items-center space-x-2">
            <i className={`las la-${data.icon} la-lg ${data.type === 'notification' ? 'text-red-600' : 'text-green-600'}`}></i>
            <span className="font-medium text-gray-900">{data.label}</span>
        </div>
        {data.description && (
            <p className="text-sm text-gray-600 mt-1">{data.description}</p>
        )}
        {data.variables && data.variables.length > 0 && (
            <div className="mt-2 pt-2 border-t border-gray-200">
                <div className="text-xs text-gray-500 mb-1">Variables:</div>
                {data.variables.map((variable, index) => (
                    <div key={index} className="text-xs text-gray-700">
                        {variable.name}: {variable.value}
                    </div>
                ))}
            </div>
        )}
        {/* Decorative dot (top only) */}
    </div>
    );
};

const StartNode = ({ data, selected, id }) => (
    <div className={`w-16 h-16 rounded-full flex items-center justify-center border-2 ${selected ? 'border-blue-500 shadow-lg' : 'border-blue-300'} bg-blue-100 relative overflow-visible`}>
        <Handle type="source" position={Position.Right} id={`out-${id}`} style={{ background: '#3b82f6' }} />
        <span className="font-medium text-blue-900 text-sm">Start</span>
    </div>
);

const GroupNode = ({ data, selected, id }) => {
    const [isCollapsed, setIsCollapsed] = useState(false);
    const [showAddAction, setShowAddAction] = useState(false);
    
    return (
        <div
            className={`border-2 rounded-lg ${selected ? 'border-blue-500 shadow-lg' : 'border-gray-300'} bg-transparent`}
            style={{ width: '100%', height: '100%', minWidth: 320, minHeight: 140 }}
        >
            <NodeResizer color="#3b82f6" isVisible={selected} minWidth={320} minHeight={140} />
            <div className="bg-gray-100 px-3 py-2 border-b border-gray-200 flex items-center justify-between">
                <h4 className="font-medium text-gray-900 text-sm truncate">{data.title}</h4>
                <div className="flex items-center space-x-2">
                    <button 
                        onClick={() => setShowAddAction(!showAddAction)}
                        className="text-blue-600 hover:text-blue-700 text-sm"
                        title="Add Action"
                    >
                        <i className="las la-plus la-sm"></i>
                    </button>
                    <button 
                        onClick={() => setIsCollapsed(!isCollapsed)}
                        className="text-gray-400 hover:text-gray-600"
                        title="Collapse/Expand"
                    >
                        <i className={`las la-chevron-${isCollapsed ? 'down' : 'up'} la-sm`}></i>
                    </button>
                </div>
            </div>
            {!isCollapsed && (
                <div className="p-3 space-y-3 h-[calc(100%-44px)]">
                    {data.children}
                    {showAddAction && (
                        <div className="p-2 border border-dashed border-gray-300 rounded-lg bg-gray-50 text-center text-gray-500 text-sm">
                            Drop actions here to add to group
                        </div>
                    )}
                </div>
            )}
        </div>
    );
};

const nodeTypes = {
    action: ActionNode,
    start: StartNode,
    group: GroupNode,
};

const MacroEditor = () => {
    const [nodes, setNodes, onNodesChange] = useNodesState([]);
    const [edges, setEdges, onEdgesChange] = useEdgesState([]);
    const [actionTypes, setActionTypes] = useState({});
    const [variables, setVariables] = useState([]);
    const [timers, setTimers] = useState([]);
    const [selectedNode, setSelectedNode] = useState(null);
    const [selectedCategory, setSelectedCategory] = useState(null);
    const [showVariableModal, setShowVariableModal] = useState(false);
    const [showTimerModal, setShowTimerModal] = useState(false);
    const [showGroupModal, setShowGroupModal] = useState(false);
    const [showActionModal, setShowActionModal] = useState(false);
    const [selectedGroupId, setSelectedGroupId] = useState(null);
    const [connectionMode, setConnectionMode] = useState(false);
    const [sourceNode, setSourceNode] = useState(null);
    const reactFlowWrapper = useRef(null);
    const [reactFlowInstance, setReactFlowInstance] = useState(null);
    const [paneEl, setPaneEl] = useState(null);
    const [viewport, setViewport] = useState({ x: 0, y: 0, zoom: 1 });

    // Load action types on component mount
    useEffect(() => {
        fetchActionTypes();
        initializeDefaultFlow();
    }, []);

    // Locate pane element for portal backgrounds
    useEffect(() => {
        if (reactFlowWrapper.current) {
            const el = reactFlowWrapper.current.querySelector('.react-flow__pane');
            if (el) setPaneEl(el);
        }
    }, [reactFlowWrapper]);

    const fetchActionTypes = async () => {
        try {
            const response = await fetch('/automation/action-types');
            const data = await response.json();
            setActionTypes(data || {});
            // Don't auto-select category - keep secondary menu hidden by default
        } catch (error) {
            console.error('Error fetching action types:', error);
            setActionTypes({});
        }
    };

    const initializeDefaultFlow = () => {
        const initialNodes = [
            {
                id: 'start',
                type: 'start',
                position: { x: 100, y: 200 },
                data: { label: 'Start' },
            },
        ];

        setNodes(initialNodes);
    };

    const onConnect = useCallback(
        (params) => {
            // Allow connections between any nodes, including cross-group connections
            setEdges((eds) => addEdge({
                ...params,
                type: 'smoothstep',
                markerEnd: {
                    type: MarkerType.ArrowClosed,
                    width: 20,
                    height: 20,
                },
                style: {
                    strokeWidth: 2,
                    stroke: '#6b7280',
                },
            }, eds));
            
            // Exit connection mode after successful connection
            setConnectionMode(false);
            setSourceNode(null);
        },
        [setEdges]
    );

    const onDragOver = useCallback((event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
    }, []);

    // Auto-size group nodes to fit their children
    const autoSizeGroups = useCallback(() => {
        setNodes((prev) => {
            const next = prev.map((node) => {
                if (node.type !== 'group') return node;
                const children = prev.filter((n) => n.parentNode === node.id);
                if (children.length === 0) {
                    const minWidth = node.data?.width || 380;
                    return { ...node, style: { ...(node.style || {}), width: minWidth, minWidth, zIndex: 0 } };
                }
                const padding = 24;
                const headerHeight = 44; // group title bar
                let maxRight = 0;
                let maxBottom = 0;
                children.forEach((c) => {
                    const cw = c.width || 320;
                    const ch = c.height || 84;
                    const cx = (c.position?.x || 0) + cw;
                    const cy = (c.position?.y || 0) + ch;
                    maxRight = Math.max(maxRight, cx);
                    maxBottom = Math.max(maxBottom, cy);
                });
                const width = Math.max(maxRight + padding, node.data?.width || 380);
                const height = Math.max(maxBottom + padding + headerHeight, 160);
                return { ...node, style: { ...(node.style || {}), width, height, zIndex: 0 } };
            });
            return next;
        });
    }, [setNodes]);

    useEffect(() => {
        // defer to ensure ReactFlow measured node dimensions
        const id = setTimeout(autoSizeGroups, 50);
        return () => clearTimeout(id);
    }, [nodes, autoSizeGroups]);

    // Elevate edges above group backgrounds while keeping nodes interactive
    useEffect(() => {
        const style = document.createElement('style');
        style.setAttribute('data-macro-editor-edges-z', '1');
        style.innerHTML = `
            /* Nodes (actions) above edges */
            .react-flow__nodes { z-index: 3 !important; }
            /* Edges above group backgrounds but below action nodes */
            .react-flow__edges, .react-flow__connection { z-index: 2 !important; pointer-events: auto; }
        `;
        document.head.appendChild(style);
        return () => {
            if (style && style.parentNode) style.parentNode.removeChild(style);
        };
    }, []);

    // Define createPredefinedGroup BEFORE onDrop so it exists for the dependency array
    const createPredefinedGroup = useCallback((groupType, position) => {
        let groupData;
        let actions = [];

        switch (groupType) {
            case 'android-vm':
                groupData = {
                    title: 'Создаём вирт. машину Android 7.1',
                    description: 'Android Virtual Machine Creation Workflow'
                };
                actions = [
                    {
                        label: 'Создаём и запускаем виртуальную машину {-Variable.vm_name-}',
                        type: 'notification',
                        icon: 'bell',
                        description: 'Creating and launching virtual machine'
                    },
                    {
                        label: 'Создание устройства',
                        type: 'action',
                        icon: 'mobile-alt',
                        description: 'Device creation'
                    },
                    {
                        label: 'Выбрать устройство',
                        type: 'action',
                        icon: 'mobile-alt',
                        description: 'Select device'
                    },
                    {
                        label: 'Установка языка',
                        type: 'action',
                        icon: 'language',
                        description: 'Language installation'
                    },
                    {
                        label: 'Разрешение экрана устройства',
                        type: 'action',
                        icon: 'desktop',
                        description: 'Device screen resolution'
                    },
                    {
                        label: 'Запуск/перезапуск устройства',
                        type: 'action',
                        icon: 'mobile-alt',
                        description: 'Device start/restart'
                    }
                ];
                break;
            case 'device-setup':
                groupData = {
                    title: 'Device Setup Workflow',
                    description: 'Complete device configuration process'
                };
                actions = [
                    {
                        label: 'Initialize Device',
                        type: 'action',
                        icon: 'mobile-alt',
                        description: 'Initialize new device'
                    },
                    {
                        label: 'Configure Settings',
                        type: 'action',
                        icon: 'cog',
                        description: 'Configure device settings'
                    },
                    {
                        label: 'Install Apps',
                        type: 'action',
                        icon: 'download',
                        description: 'Install required applications'
                    }
                ];
                break;
            default:
                return;
        }

        const groupId = `group-${Date.now()}`;
        
        // Create the group node first
        const groupNode = {
            id: groupId,
            type: 'group',
            position,
            data: { 
                title: groupData.title,
                children: [],
                width: 380,
            },
            style: { width: 380, height: 200, zIndex: 0 },
        };

        // Create action nodes as children of the group
        const actionNodes = actions.map((action, index) => ({
            id: `action-${groupId}-${index}`,
            type: 'action',
            position: { x: 20, y: 20 + (index * 80) },
            data: {
                label: action.label,
                type: action.type,
                icon: action.icon,
                description: action.description,
                variables: [],
            },
            parentNode: groupId,
            extent: 'parent',
        }));

        // Add all nodes at once to maintain proper parent-child relationships
        setNodes((nds) => nds.concat([groupNode, ...actionNodes]));

        // Create connections between actions in sequence
        const newEdges = [];
        for (let i = 0; i < actionNodes.length - 1; i++) {
            newEdges.push({
                id: `edge-${actionNodes[i].id}-${actionNodes[i + 1].id}`,
                source: actionNodes[i].id,
                target: actionNodes[i + 1].id,
                type: 'smoothstep',
                markerEnd: {
                    type: MarkerType.ArrowClosed,
                    width: 20,
                    height: 20,
                },
                style: {
                    strokeWidth: 2,
                    stroke: '#6b7280',
                },
            });
        }

        // Add edges
        setEdges((eds) => eds.concat(newEdges));
        // size group to children
        setTimeout(() => autoSizeGroups(), 50);
    }, [setNodes, setEdges, autoSizeGroups]);

    const onDrop = useCallback(
        (event) => {
            event.preventDefault();

            const type = event.dataTransfer.getData('application/reactflow');
            const actionKey = event.dataTransfer.getData('actionKey');
            const actionLabel = event.dataTransfer.getData('actionLabel');
            const isGroup = event.dataTransfer.getData('isGroup') === 'true';
            const targetGroupId = event.dataTransfer.getData('targetGroupId');
            const groupType = event.dataTransfer.getData('groupType');

            if (typeof type === 'undefined' || !type) {
                return;
            }

            if (reactFlowWrapper.current && reactFlowInstance) {
                const reactFlowBounds = reactFlowWrapper.current.getBoundingClientRect();
                const position = reactFlowInstance.project({
                    x: event.clientX - reactFlowBounds.left,
                    y: event.clientY - reactFlowBounds.top,
                });

                if (isGroup && groupType) {
                    // Create predefined group with proper structure
                    createPredefinedGroup(groupType, position);
                    // allow group and children to mount, then size
                    setTimeout(() => autoSizeGroups(), 50);
                } else if (isGroup) {
                    // Create generic empty group
                    const groupId = `group-${Date.now()}`;
                    const groupNode = {
                        id: groupId,
                        type: 'group',
                        position,
                        data: { 
                            title: actionLabel || 'Group',
                            children: [],
                            width: 380,
                        },
                        style: { width: 380, height: 200, zIndex: 0 },
                    };
                    setNodes((nds) => nds.concat(groupNode));
                    setTimeout(() => autoSizeGroups(), 50);
                } else {
                    // Determine if dropping into a group by hit-testing bounds
                    const groups = nodes.filter((n) => n.type === 'group');
                    let dropGroup = null;
                    for (const g of groups) {
                        const gw = (g.style && g.style.width) || g.data?.width || 380;
                        const gh = (g.style && g.style.height) || 240;
                        const gx = g.position?.x || 0;
                        const gy = g.position?.y || 0;
                        if (position.x >= gx && position.x <= gx + gw && position.y >= gy && position.y <= gy + gh) {
                            dropGroup = g;
                            break;
                        }
                    }

                    // Create an action node
                    const newNode = {
                        id: `${type}-${Date.now()}`,
                        type: 'action',
                        position: dropGroup
                            ? {
                                  // position relative to group interior
                                  x: position.x - (dropGroup.position?.x || 0) - 16,
                                  y: position.y - (dropGroup.position?.y || 0) - 56,
                              }
                            : position,
                        data: {
                            label: actionLabel,
                            type: 'action',
                            icon: actionKey ? getIconForAction(actionKey) : 'cog',
                            description: `Execute ${actionLabel?.toLowerCase?.() || ''}`,
                            variables: [],
                            hasOutput: true, // Ensure output connection point is shown
                            hasInput: true,  // Ensure input connection point is shown
                        },
                        // If dropping into a group, set parent
                        ...(dropGroup || targetGroupId
                            ? { parentNode: (dropGroup && dropGroup.id) || targetGroupId, extent: 'parent' }
                            : {}),
                    };
                    setNodes((nds) => nds.concat(newNode));

                    if (dropGroup) setTimeout(() => autoSizeGroups(), 50);
                }
            }
        },
        [reactFlowInstance, setNodes, createPredefinedGroup, nodes, autoSizeGroups]
    );

    const getIconForAction = (actionKey) => {
        const iconMap = {
            'create_device': 'mobile-alt',
            'select_device': 'mobile-alt',
            'start_device': 'play',
            'stop_device': 'stop',
            'restart_device': 'redo',
            'install_language': 'language',
            'set_resolution': 'desktop',
            'read_file': 'file',
            'write_file': 'file-alt',
            'delete_file': 'trash',
            'send_sms': 'comment',
            'read_sms': 'comment-dots',
            'query_database': 'database',
            'extract_text': 'edit',
            'parse_json': 'code',
            'resize_image': 'image',
            'set_variable': 'cogs',
            'wait': 'clock',
        };
        return iconMap[actionKey] || 'cog';
    };

    const handleNodeClick = (event, node) => {
        // Click-to-connect workflow
        if (connectionMode) {
            // First click: set source
            if (!sourceNode) {
                setSourceNode(node);
                return;
            }
            // Second click: create edge if different node
            if (sourceNode && sourceNode.id !== node.id) {
                const newEdge = {
                    id: `edge-${sourceNode.id}-${node.id}`,
                    source: sourceNode.id,
                    target: node.id,
                    type: 'smoothstep',
                    markerEnd: {
                        type: MarkerType.ArrowClosed,
                        width: 20,
                        height: 20,
                    },
                    style: {
                        strokeWidth: 2,
                        stroke: '#6b7280',
                    },
                };
                setEdges((eds) => eds.concat(newEdge));
            }
            // Exit connection mode after attempting a connection
            setConnectionMode(false);
            setSourceNode(null);
            return;
        }

        // Default behavior: select node, manage group selection
        setSelectedNode(node);
        if (node.type === 'group') {
            setSelectedGroupId(node.id);
        }
    };

    const handleConnectionPointClick = (event, nodeId, isOutput) => {
        event.stopPropagation();
        
        if (connectionMode) {
            if (sourceNode && sourceNode.id !== nodeId) {
                // Create connection
                const newEdge = {
                    id: `edge-${sourceNode.id}-${nodeId}`,
                    source: sourceNode.id,
                    target: nodeId,
                    type: 'smoothstep',
                    markerEnd: {
                        type: MarkerType.ArrowClosed,
                        width: 20,
                        height: 20,
                    },
                    style: {
                        strokeWidth: 2,
                        stroke: '#6b7280',
                    },
                };
                setEdges((eds) => eds.concat(newEdge));
                
                // Exit connection mode
                setConnectionMode(false);
                setSourceNode(null);
            }
        } else {
            // Enter connection mode
            setConnectionMode(true);
            setSourceNode({ id: nodeId, isOutput });
        }
    };

    const handleCategoryClick = (categoryKey) => {
        setSelectedCategory(selectedCategory === categoryKey ? null : categoryKey);
    };

    const addVariable = (variable) => {
        setVariables([...variables, variable]);
        if (selectedNode) {
            const updatedNodes = nodes.map(node => {
                if (node.id === selectedNode.id) {
                    return {
                        ...node,
                        data: {
                            ...node.data,
                            variables: [...(node.data.variables || []), variable]
                        }
                    };
                }
                return node;
            });
            setNodes(updatedNodes);
        }
    };

    const addTimer = (timer) => {
        setTimers([...timers, timer]);
    };

    const createGroup = (groupData) => {
        const groupId = `group-${Date.now()}`;
        const groupNode = {
            id: groupId,
            type: 'group',
            position: { x: 200, y: 200 },
            data: { 
                title: groupData.title,
                children: [],
                width: 380,
            },
            style: { width: 380, height: 200, zIndex: 0 },
        };
        setNodes((nds) => nds.concat(groupNode));
        setShowGroupModal(false);
    };

    const addActionToGroup = (groupId, actionData) => {
        const actionId = `action-${Date.now()}`;
        const actionNode = {
            id: actionId,
            type: 'action',
            position: { x: 0, y: 0 }, // Position relative to group
            data: {
                label: actionData.label,
                type: actionData.type || 'action',
                icon: actionData.icon,
                description: actionData.description,
                variables: [],
            },
            parentNode: groupId,
            extent: 'parent',
        };

        setNodes((nds) => nds.concat(actionNode));
    };

    const saveMacro = async () => {
        const macroData = {
            name: document.querySelector('input[placeholder="Macro Name"]')?.value || 'Untitled Macro',
            description: '',
            nodes: nodes,
            connections: edges,
            variables: variables,
            timers: timers,
        };

        try {
            const response = await fetch('/automation/macros', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify(macroData),
            });

            if (response.ok) {
                const result = await response.json();
                alert('Macro saved successfully!');
            } else {
                alert('Error saving macro');
            }
        } catch (error) {
            console.error('Error saving macro:', error);
            alert('Error saving macro');
        }
    };

    // Make action items draggable
    const handleActionDragStart = (event, actionKey, actionLabel) => {
        event.dataTransfer.setData('application/reactflow', 'action');
        event.dataTransfer.setData('actionKey', actionKey);
        event.dataTransfer.setData('actionLabel', actionLabel);
        event.dataTransfer.setData('isGroup', 'false');
        event.dataTransfer.effectAllowed = 'move';
    };

    // Make group items draggable
    const handleGroupDragStart = (event, groupType) => {
        event.dataTransfer.setData('application/reactflow', 'group');
        event.dataTransfer.setData('groupType', groupType);
        event.dataTransfer.setData('isGroup', 'true');
        event.dataTransfer.effectAllowed = 'move';
    };

    const cancelConnectionMode = () => {
        setConnectionMode(false);
        setSourceNode(null);
    };

    return (
        <div className="h-full">
            <div className="flex h-full">
                {/* Left Sidebar - Categories */}
                <div className="w-80 bg-white border-r border-gray-200 flex flex-col">
                    {/* Search Bar */}
                    <div className="p-4 border-b border-gray-200">
                        <div className="relative">
                            <input 
                                type="text" 
                                placeholder="Enter text to search..." 
                                className="w-full pr-4 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                style={{ paddingLeft: '2.5rem' }}
                            />
                            <div className="absolute top-0 left-0 h-full w-14 flex items-center justify-center pointer-events-none px-3">
                                <i className="las la-search la-sm text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    {/* Categories */}
                    <div className="flex-1 overflow-y-auto p-4 space-y-4">
                        {Object.keys(actionTypes).length === 0 && (
                            <div className="text-sm text-gray-500">No categories available.</div>
                        )}
                        {Object.entries(actionTypes).map(([categoryKey, category]) => (
                            <div key={categoryKey} className="space-y-2">
                                <div 
                                    className="flex items-center justify-between cursor-pointer hover:bg-gray-50 p-2 rounded"
                                    onClick={() => handleCategoryClick(categoryKey)}
                                >
                                    <h4 className="text-sm font-medium text-gray-900">{category.label}</h4>
                                    <button className="text-gray-400 hover:text-gray-600">
                                        <i className={`las la-chevron-${selectedCategory === categoryKey ? 'down' : 'up'} la-sm`}></i>
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Right Sidebar - Actions (only visible when category selected) */}
                {selectedCategory && actionTypes[selectedCategory] && (
                    <div className="w-80 bg-white border-r border-gray-200 flex flex-col z-10">
                        <div className="p-4 border-b border-gray-200">
                            <div className="flex items-center justify-between">
                                <h3 className="text-lg font-medium text-gray-900">{actionTypes[selectedCategory].label}</h3>
                                <button 
                                    onClick={() => setSelectedCategory(null)}
                                    className="text-gray-400 hover:text-gray-600"
                                >
                                    <i className="las la-times la-lg"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div className="flex-1 overflow-y-auto p-4">
                            <div className="space-y-2">
                                {/* Predefined Groups */}
                                <div className="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <h4 className="text-sm font-medium text-blue-900 mb-2">Predefined Groups</h4>
                                    <div className="space-y-2">
                                        <div 
                                            className="flex items-center space-x-2 p-2 hover:bg-blue-100 rounded cursor-pointer"
                                            draggable
                                            onDragStart={(e) => handleGroupDragStart(e, 'android-vm')}
                                        >
                                            <i className="las la-layer-group la-sm text-blue-600"></i>
                                            <span className="text-sm text-blue-700">Android VM Creation</span>
                                        </div>
                                        <div 
                                            className="flex items-center space-x-2 p-2 hover:bg-blue-100 rounded cursor-pointer"
                                            draggable
                                            onDragStart={(e) => handleGroupDragStart(e, 'device-setup')}
                                        >
                                            <i className="las la-layer-group la-sm text-blue-600"></i>
                                            <span className="text-sm text-blue-700">Device Setup</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Individual Actions */}
                                <div>
                                    <h4 className="text-sm font-medium text-gray-900 mb-2">Individual Actions</h4>
                                    <div className="space-y-2">
                                        {Object.entries(actionTypes[selectedCategory].actions).map(([actionKey, actionLabel]) => (
                                            <div 
                                                key={actionKey}
                                                className="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded cursor-pointer"
                                                draggable
                                                onDragStart={(e) => handleActionDragStart(e, actionKey, actionLabel)}
                                            >
                                                <i className={`las la-${actionTypes[selectedCategory].icon} la-sm text-gray-600`}></i>
                                                <span className="text-sm text-gray-700">{actionLabel}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Main Canvas Area */}
                <div className={`flex-1 bg-gray-50 relative ${selectedCategory ? '' : 'ml-0'}`} ref={reactFlowWrapper}>
                    {/* Connection Mode Indicator */}
                    {connectionMode && (
                        <div className="absolute top-4 left-1/2 transform -translate-x-1/2 bg-yellow-100 border border-yellow-300 rounded-lg px-4 py-2 z-30">
                            <div className="flex items-center space-x-2">
                                <i className="las la-link la-sm text-yellow-700"></i>
                                <span className="text-sm text-yellow-700 font-medium">
                                    Connection Mode: Click on target block to connect from {sourceNode?.id}
                                </span>
                                <button 
                                    onClick={cancelConnectionMode}
                                    className="text-yellow-700 hover:text-yellow-800"
                                >
                                    <i className="las la-times la-sm"></i>
                                </button>
                            </div>
                        </div>
                    )}
                    
                    {/* Debug Info */}
                    <div className="absolute top-4 right-4 bg-gray-100 border border-gray-300 rounded px-2 py-1 text-xs text-gray-600 z-30">
                        Connection Mode: {connectionMode ? 'ON' : 'OFF'}
                    </div>
                    
                    {/* Connection Points Debug */}
                    <div className="absolute top-16 right-4 bg-gray-100 border border-gray-300 rounded px-2 py-1 text-xs text-gray-600 z-30">
                        Nodes: {nodes.length} | Edges: {edges.length}
                    </div>

                    <ReactFlow
                        nodes={nodes}
                        edges={edges}
                        onNodesChange={onNodesChange}
                        onEdgesChange={onEdgesChange}
                        onConnect={onConnect}
                        onInit={(inst) => { setReactFlowInstance(inst); }}
                        onMove={(_, vp) => setViewport(vp)}
                        onDrop={onDrop}
                        onDragOver={onDragOver}
                        nodeTypes={nodeTypes}
                        onNodeClick={handleNodeClick}
                        fitView
                        className="bg-gray-50"
                    >
                        <Controls />
                        <MiniMap />
                        <Background variant="dots" gap={12} size={1} />
                    </ReactFlow>

                    {/* Group backgrounds rendered in pane (below edges) */}
                    {paneEl && createPortal(
                        <div
                            style={{
                                position: 'absolute',
                                left: 0,
                                top: 0,
                                transform: `translate(${viewport.x}px, ${viewport.y}px) scale(${viewport.zoom})`,
                                transformOrigin: '0 0',
                                pointerEvents: 'none',
                            }}
                        >
                            {nodes.filter(n => n.type === 'group').map(g => (
                                <div
                                    key={`bg-${g.id}`}
                                    style={{
                                        position: 'absolute',
                                        left: (g.position?.x || 0),
                                        top: (g.position?.y || 0),
                                        width: (g.style?.width || g.data?.width || 380),
                                        height: (g.style?.height || 200),
                                        background: 'white',
                                        borderRadius: 12,
                                        boxShadow: '0 1px 1px rgba(0,0,0,0.02)',
                                        border: '1px solid rgba(17,24,39,0.15)',
                                    }}
                                />
                            ))}
                        </div>,
                        paneEl
                    )}

                </div>
                
                {/* Bottom Toolbar */}
                <div className="absolute bottom-8 left-1/2 transform -translate-x-1/2 flex items-center space-x-4 bg-white rounded-lg shadow-lg px-4 py-2 z-50">
                    <button 
                        onClick={() => setConnectionMode(!connectionMode)}
                        className={`${connectionMode ? 'bg-blue-600 text-white' : 'text-gray-600 hover:text-gray-800'} px-3 py-2 rounded`}
                        title="Toggle Connection Mode"
                    >
                        <i className="las la-link la-lg"></i>
                    </button>
                    <button 
                        onClick={() => setShowGroupModal(true)}
                        className="text-gray-600 hover:text-gray-800 px-3 py-2 rounded"
                        title="Create New Group"
                    >
                        <i className="las la-layer-group la-lg"></i>
                    </button>
                </div>
            </div>

            {/* Group Creation Modal */}
            {showGroupModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-6 w-96">
                        <h3 className="text-lg font-medium mb-4">Create New Group</h3>
                        <div className="space-y-4">
                            <input 
                                type="text" 
                                placeholder="Group title" 
                                className="w-full border border-gray-300 rounded-md px-3 py-2"
                                id="groupTitle"
                            />
                            <textarea 
                                placeholder="Group description (optional)" 
                                className="w-full border border-gray-300 rounded-md px-3 py-2"
                                rows="3"
                                id="groupDescription"
                            />
                        </div>
                        <div className="flex justify-end space-x-2 mt-6">
                            <button 
                                onClick={() => setShowGroupModal(false)}
                                className="px-4 py-2 text-gray-600 hover:text-gray-800"
                            >
                                Cancel
                            </button>
                            <button 
                                onClick={() => {
                                    const title = document.getElementById('groupTitle').value;
                                    const description = document.getElementById('groupDescription').value;
                                    if (title.trim()) {
                                        createGroup({ title: title.trim(), description });
                                    }
                                }}
                                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            >
                                Create Group
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Variable Modal */}
            {showVariableModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-6 w-96">
                        <h3 className="text-lg font-medium mb-4">Add Variable</h3>
                        <div className="space-y-4">
                            <input 
                                type="text" 
                                placeholder="Variable name" 
                                className="w-full border border-gray-300 rounded-md px-3 py-2"
                            />
                            <select className="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="string">String</option>
                                <option value="integer">Integer</option>
                                <option value="boolean">Boolean</option>
                                <option value="array">Array</option>
                            </select>
                            <input 
                                type="text" 
                                placeholder="Default value" 
                                className="w-full border border-gray-300 rounded-md px-3 py-2"
                            />
                        </div>
                        <div className="flex justify-end space-x-2 mt-6">
                            <button 
                                onClick={() => setShowVariableModal(false)}
                                className="px-4 py-2 text-gray-600 hover:text-gray-800"
                            >
                                Cancel
                            </button>
                            <button 
                                onClick={() => {
                                    addVariable({
                                        name: 'New Variable',
                                        type: 'string',
                                        value: ''
                                    });
                                    setShowVariableModal(false);
                                }}
                                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            >
                                Add
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Timer Modal */}
            {showTimerModal && (
                <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div className="bg-white rounded-lg p-6 w-96">
                        <h3 className="text-lg font-medium mb-4">Add Timer</h3>
                        <div className="space-y-4">
                            <input 
                                type="text" 
                                placeholder="Timer name" 
                                className="w-full border border-gray-300 rounded-md px-3 py-2"
                            />
                            <div className="flex space-x-2">
                                <input 
                                    type="number" 
                                    placeholder="Delay" 
                                    className="flex-1 border border-gray-300 rounded-md px-3 py-2"
                                />
                                <select className="border border-gray-300 rounded-md px-3 py-2">
                                    <option value="string">Seconds</option>
                                    <option value="integer">Minutes</option>
                                    <option value="boolean">Hours</option>
                                    <option value="array">Days</option>
                                </select>
                            </div>
                        </div>
                        <div className="flex justify-end space-x-2 mt-6">
                            <button 
                                onClick={() => setShowTimerModal(false)}
                                className="px-4 py-2 text-gray-600 hover:text-gray-800"
                            >
                                Cancel
                            </button>
                            <button 
                                onClick={() => {
                                    addTimer({
                                        name: 'New Timer',
                                        delay: 5,
                                        unit: 'seconds'
                                    });
                                    setShowTimerModal(false);
                                }}
                                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                            >
                                Add
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

// Initialize the React component
if (document.getElementById('react-macro-editor') && !window.macroEditorInitialized) {
    try {
        window.macroEditorInitialized = true;
        const root = ReactDOM.createRoot(document.getElementById('react-macro-editor'));
        root.render(<MacroEditor />);
        console.log('MacroEditor successfully initialized');
    } catch (error) {
        console.error('Error initializing MacroEditor:', error);
        window.macroEditorInitialized = false;
    }
}
