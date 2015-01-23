App.gingerTypeName = function (gingerType) {
    return ginger_type_name(gingerType, App.GingerTypes);
};

App.connectorName = function (connectorId) {
    return connector_name(connectorId, App.Connectors);
}

App.connectorIcon = function (connectorId, defaultIcon) {
    return connector_icon(connectorId, App.Connectors, defaultIcon);
}

