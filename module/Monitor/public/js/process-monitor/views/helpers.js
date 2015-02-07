App.processingTypeName = function (processingType) {
    return processing_type_name(processingType, App.ProcessingTypes);
};

App.connectorName = function (connectorId) {
    return connector_name(connectorId, App.Connectors);
}

App.connectorIcon = function (connectorId, defaultIcon) {
    return connector_icon(connectorId, App.Connectors, defaultIcon);
}

